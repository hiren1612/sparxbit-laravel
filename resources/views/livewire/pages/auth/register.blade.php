<?php

use App\Mail\MagicLinkMail;
use App\Models\User;
use App\Services\MagicLinkService;
use App\Services\UserProvisioning;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.sparx-guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $showPassword = false;
    public bool $magicSent = false;
    public ?string $devLink = null;

    public function sendMagicLink(): void
    {
        $this->validateOnly('email', ['email' => 'required|email']);
        $email = strtolower(trim($this->email));

        $key = 'magic:'.$email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('email', 'Too many requests. Please wait a minute and try again.');

            return;
        }
        RateLimiter::hit($key, 60);

        $url = MagicLinkService::issue($email);
        Mail::to($email)->send(new MagicLinkMail($url));

        $this->magicSent = true;
        // Only surface the link on-screen when mail has no real inbox (log driver).
        if (config('mail.default') === 'log') {
            $this->devLink = $url;
        }
    }

    public function reset_magic(): void
    {
        $this->magicSent = false;
        $this->devLink = null;
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));
        UserProvisioning::seedNewUser($user);
        UserProvisioning::attributeReferral($user, session()->pull('referral_code'));
        Auth::login($user);

        $this->redirect(route('welcome', absolute: false), navigate: true);
    }
}; ?>

<div>
    @if ($magicSent)
        <div style="text-align:center;">
            <div style="width:60px; height:60px; border-radius:18px; background:var(--primary-lt); display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 16px;">📬</div>
            <h1 style="font-size:20px; font-weight:800; color:var(--text); letter-spacing:-.4px; margin-bottom:6px;">Check your email</h1>
            <p style="font-size:13px; color:var(--text-meta); line-height:1.6; margin-bottom:20px;">We sent a secure sign-in link to<br><strong style="color:var(--text);">{{ $email }}</strong>. Tap it to finish creating your account.</p>
            @if ($devLink)
                <a href="{{ $devLink }}" class="btn btn--grad" style="display:block; margin-bottom:12px;">Open sign-in link (local)</a>
                <p style="font-size:10.5px; color:var(--text-meta); margin-bottom:16px;">Shown only in local dev because mail goes to the log.</p>
            @endif
            <button type="button" wire:click="reset_magic" style="background:none; border:none; color:var(--primary); font-size:13px; font-weight:600; cursor:pointer; font-family:inherit;">← Use a different email</button>
        </div>
    @else
        <h1 style="font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.5px; margin-bottom:4px;">Create your account</h1>
        <p style="font-size:12.5px; color:var(--text-meta); margin-bottom:22px;">Start building better days, one Bit at a time.</p>

        {{-- Magic link --}}
        <form wire:submit="sendMagicLink">
            <div class="field-label">Email address</div>
            <div class="input-row" style="margin-bottom:4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
                <input wire:model="email" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com">
            </div>
            @error('email') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:14px;"></div> @enderror

            <button type="submit" class="btn btn--grad">
                <span wire:loading.remove wire:target="sendMagicLink">Continue with Magic Link</span>
                <span wire:loading wire:target="sendMagicLink">Sending…</span>
            </button>
        </form>

        <div class="or-divider"><span>OR</span></div>

        <a href="{{ route('social.redirect', 'google') }}" class="btn btn--secondary" style="margin-bottom:10px; text-decoration:none;">
            <svg width="16" height="16" viewBox="0 0 18 18" fill="none"><path d="M17.1 9.2c0-.6-.05-1.2-.15-1.75H9v3.25h4.45c-.2 1.1-.85 2.02-1.8 2.62v2.1h2.85c1.63-1.48 2.6-3.66 2.6-6.22Z" fill="#4285F4"/><path d="M9 17.5c2.3 0 4.24-.76 5.5-2.08l-2.85-2.1c-.72.48-1.63.78-2.65.78-2.22 0-4.11-1.5-4.78-3.52H1.28v2.16A8.5 8.5 0 0 0 9 17.5Z" fill="#34A853"/><path d="M4.22 10.58A5.1 5.1 0 0 1 3.95 9c0-.56.1-1.1.27-1.58V5.26H1.28A8.5 8.5 0 0 0 .41 9c0 1.35.31 2.62.87 3.74l2.94-2.16Z" fill="#FBBC05"/><path d="M9 3.9c1.25 0 2.37.43 3.25 1.27l2.44-2.44A8.1 8.1 0 0 0 9 .5 8.5 8.5 0 0 0 1.28 5.26l2.94 2.16C4.89 5.4 6.78 3.9 9 3.9Z" fill="#EA4335"/></svg>
            Continue with Google
        </a>
        <a href="{{ route('social.redirect', 'apple') }}" class="btn btn--dark" style="margin-bottom:14px; text-decoration:none;">
            <svg width="14" height="16" viewBox="0 0 17 20" fill="#fff"><path d="M13.8 10.6c-.02-2 1.28-2.98 1.34-3.02-.84-1.22-2.14-1.38-2.59-1.4-1.13-.11-2.22.66-2.79.66-.58 0-1.48-.65-2.42-.63-1.24.01-2.39.69-3.03 1.77-1.32 2.19-.33 5.46.95 7.26.64.88 1.4 1.88 2.4 1.85.98-.04 1.34-.61 2.51-.61 1.16 0 1.5.61 2.51.59 1.04-.02 1.7-.93 2.32-1.82.73-1.03 1.03-2.02 1.05-2.07-.04-.02-2.23-.88-2.25-2.58ZM11.74 4.68c.54-.66.9-1.57.79-2.48-.77.04-1.71.53-2.26 1.18-.51.59-.93 1.52-.81 2.42.85.06 1.73-.46 2.28-1.12Z"/></svg>
            Sign up with Apple
        </a>

        {{-- Password fallback --}}
        @if ($showPassword)
            <form wire:submit="register" style="margin-top:6px; padding-top:14px; border-top:1px solid var(--border);">
                <div class="field-label">Name</div>
                <div class="input-row" style="margin-bottom:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    <input wire:model="name" type="text" name="name" autocomplete="name" placeholder="Your name">
                </div>
                @error('name') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:14px;"></div> @enderror

                <div class="field-label">Password</div>
                <div class="input-row" style="margin-bottom:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input wire:model="password" type="password" name="password" autocomplete="new-password" placeholder="Create a password">
                </div>
                @error('password') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:14px;"></div> @enderror

                <div class="field-label">Confirm password</div>
                <div class="input-row" style="margin-bottom:18px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input wire:model="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat your password">
                </div>

                <button type="submit" class="btn btn--grad">Create account</button>
            </form>
        @else
            <p style="font-size:12px; color:var(--text-meta); text-align:center;">
                Prefer a password?
                <button type="button" wire:click="$set('showPassword', true)" style="background:none; border:none; color:var(--primary); font-weight:600; cursor:pointer; font-family:inherit; font-size:12px;">Sign up with email</button>
            </p>
        @endif

        <p style="font-size:12.5px; color:var(--text-meta); text-align:center; margin-top:18px;">
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate style="color:var(--primary); font-weight:600; text-decoration:none;">Sign in</a>
        </p>
    @endif
</div>
