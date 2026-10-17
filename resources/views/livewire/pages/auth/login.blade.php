<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.sparx-guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 style="font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.5px; margin-bottom:4px;">Welcome back</h1>
    <p style="font-size:12.5px; color:var(--text-meta); margin-bottom:22px;">Sign in to keep your streak going.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <div class="field-label">Email address</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
            <input wire:model="form.email" type="email" name="email" required autofocus autocomplete="username" placeholder="you@example.com">
        </div>
        @error('form.email') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:16px;"></div> @enderror

        <div class="field-label">Password</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input wire:model="form.password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </div>
        @error('form.password') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:14px;"></div> @enderror

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
            <label style="display:inline-flex; align-items:center; gap:7px; cursor:pointer; font-size:12.5px; color:var(--text-mid);">
                <input wire:model="form.remember" type="checkbox" name="remember" style="width:16px; height:16px; accent-color:var(--primary);">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate style="font-size:12.5px; font-weight:600; color:var(--primary); text-decoration:none;">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn--grad">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>

    <p style="font-size:12.5px; color:var(--text-meta); text-align:center; margin-top:20px;">
        New to Sparx Bit?
        <a href="{{ route('register') }}" wire:navigate style="color:var(--primary); font-weight:600; text-decoration:none;">Create an account</a>
    </p>
</div>
