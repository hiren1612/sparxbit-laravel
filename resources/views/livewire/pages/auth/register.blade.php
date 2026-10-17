<?php

use App\Models\User;
use App\Services\UserProvisioning;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.sparx-guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        // Seed the new account: system categories, Daily Check-In bit, referral code.
        UserProvisioning::seedNewUser($user);

        Auth::login($user);

        $this->redirect(route('welcome', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 style="font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.5px; margin-bottom:4px;">Create your account</h1>
    <p style="font-size:12.5px; color:var(--text-meta); margin-bottom:22px;">Start building better days, one Bit at a time.</p>

    <form wire:submit="register">
        <div class="field-label">Name</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            <input wire:model="name" type="text" name="name" required autofocus autocomplete="name" placeholder="Your name">
        </div>
        @error('name') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

        <div class="field-label">Email address</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
            <input wire:model="email" type="email" name="email" required autocomplete="username" placeholder="you@example.com">
        </div>
        @error('email') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

        <div class="field-label">Password</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input wire:model="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a password">
        </div>
        @error('password') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

        <div class="field-label">Confirm password</div>
        <div class="input-row" style="margin-bottom:18px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input wire:model="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password">
        </div>

        <button type="submit" class="btn btn--grad">
            <span wire:loading.remove wire:target="register">Create account</span>
            <span wire:loading wire:target="register">Creating…</span>
        </button>
    </form>

    <p style="font-size:12.5px; color:var(--text-meta); text-align:center; margin-top:20px;">
        Already have an account?
        <a href="{{ route('login') }}" wire:navigate style="color:var(--primary); font-weight:600; text-decoration:none;">Sign in</a>
    </p>
</div>
