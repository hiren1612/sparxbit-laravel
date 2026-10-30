<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\MagicLinkMail;
use App\Models\User;
use App\Services\MagicLinkService;
use App\Services\UserProvisioning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

// Passwordless + social sign-in for the mobile app. Every success returns the
// same shape as /api/auth/login: { user, token, accessToken } (Sanctum token).
class MobileAuthController extends Controller
{
    // Email a magic link whose button opens the Sparx Bit app.
    public function magicRequest(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email|max:254']);
        $email = strtolower(trim($data['email']));

        $url = MagicLinkService::issue($email, forApp: true);
        Mail::to($email)->send(new MagicLinkMail($url));

        // Same response whether or not the account exists (no email probing).
        return response()->json(['ok' => true]);
    }

    // Exchange the token from the magic link for a session.
    public function magicVerify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:128',
            'ref' => 'nullable|string|max:32',
        ]);

        $user = MagicLinkService::consume($data['token']);
        if (! $user) {
            return response()->json(['message' => 'That sign-in link is invalid or has expired. Please request a new one.'], 401);
        }

        return $this->signIn($user, $data['ref'] ?? null);
    }

    // Sign in with a token from the native Google / Apple SDK on the phone.
    public function social(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider' => 'required|in:google,apple',
            'token' => 'required|string|max:8192',
            'nonce' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:60', // Apple only sends the name on first sign-in
            'ref' => 'nullable|string|max:32',
        ]);

        $identity = $data['provider'] === 'google'
            ? $this->verifyGoogle($data['token'])
            : $this->verifyApple($data['token'], $data['nonce'] ?? null);

        if (! $identity) {
            return response()->json(['message' => 'Could not verify your '.ucfirst($data['provider']).' sign-in. Please try again.'], 401);
        }
        if (empty($identity['email'])) {
            return response()->json(['message' => ucfirst($data['provider']).' did not share an email address.'], 422);
        }

        $email = strtolower($identity['email']);
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'email' => $email,
                'name' => ($data['name'] ?? null) ?: (($identity['name'] ?? null) ?: Str::of($email)->before('@')->toString()),
                'password' => Hash::make(Str::random(40)),
            ]);
            UserProvisioning::seedNewUser($user);
        }

        return $this->signIn($user, $data['ref'] ?? null);
    }

    // Google ID token → verified claims. Checks signature/expiry via Google's
    // tokeninfo endpoint, then that it was issued to one of OUR client IDs so a
    // token minted for another app can't be replayed here.
    private function verifyGoogle(string $idToken): ?array
    {
        try {
            $res = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
        } catch (\Throwable $e) {
            return null;
        }
        if (! $res->ok()) {
            return null;
        }
        $claims = $res->json();
        $allowed = array_filter(array_merge(
            [config('services.google.client_id')],
            (array) config('services.google.mobile_client_ids', [])
        ));
        $issuerOk = in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
        $verified = in_array($claims['email_verified'] ?? false, [true, 'true'], true);

        if (! $issuerOk || ! in_array($claims['aud'] ?? '', $allowed, true) || ! $verified) {
            return null;
        }

        return ['email' => $claims['email'] ?? null, 'name' => $claims['name'] ?? null];
    }

    // Apple identity token → verified via Apple's public keys (audience must be
    // the web Services ID or the iOS bundle ID; nonce checked when provided).
    private function verifyApple(string $identityToken, ?string $nonce): ?array
    {
        try {
            $user = Socialite::driver('apple')->userByIdentityToken($identityToken, $nonce);
        } catch (\Throwable $e) {
            return null;
        }

        return ['email' => $user->getEmail(), 'name' => $user->getName()];
    }

    private function signIn(User $user, ?string $ref): JsonResponse
    {
        if ($user->status === 'deleted') {
            return response()->json(['message' => 'This account has been deleted.'], 401);
        }
        if ($user->status === 'archived') {
            $user->update(['status' => 'active', 'archived_at' => null]);
        }
        if ($user->wasRecentlyCreated) {
            if (! $user->referral_code) {
                $user->update(['referral_code' => UserProvisioning::genReferralCode()]);
            }
            UserProvisioning::attributeReferral($user, $ref);
        }

        $token = $user->createToken('client')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->fresh()),
            'token' => $token,
            'accessToken' => $token,
        ]);
    }
}
