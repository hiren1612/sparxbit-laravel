<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:254',
            'password' => 'required|string|min:8|max:128',
            'fullName' => 'nullable|string|max:60',
            'ref' => 'nullable|string|max:32',
        ]);
        $email = strtolower(trim($data['email']));
        if (User::where('email', $email)->exists()) {
            return response()->json(['message' => 'An account with this email already exists.'], 409);
        }
        $user = User::create([
            'email' => $email,
            'password' => Hash::make($data['password']),
            'name' => $data['fullName'] ?? null,
            'referral_code' => UserProvisioning::genReferralCode(),
        ]);
        UserProvisioning::seedNewUser($user);
        UserProvisioning::attributeReferral($user, $data['ref'] ?? null);

        $token = $user->createToken('client')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'accessToken' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', strtolower(trim($data['email'])))->first();
        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }
        if ($user->status === 'deleted') {
            return response()->json(['message' => 'This account has been deleted.'], 401);
        }
        if ($user->status === 'archived') {
            $user->update(['status' => 'active', 'archived_at' => null]);
        }

        $token = $user->createToken('client')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'accessToken' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }
}
