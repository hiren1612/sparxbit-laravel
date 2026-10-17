<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.sparx-guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <h1 style="font-size:22px; font-weight:800; color:var(--text); letter-spacing:-.5px; margin-bottom:4px;">Reset your password</h1>
    <p style="font-size:12.5px; color:var(--text-meta); margin-bottom:22px; line-height:1.6;">Enter your email and we'll send you a secure link to choose a new password.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <div class="field-label">Email address</div>
        <div class="input-row" style="margin-bottom:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-meta)" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7 10-7"/></svg>
            <input wire:model="email" type="email" name="email" required autofocus placeholder="you@example.com">
        </div>
        @error('email') <div style="color:var(--danger); font-size:11px; margin-bottom:12px;">{{ $message }}</div> @else <div style="margin-bottom:18px;"></div> @enderror

        <button type="submit" class="btn btn--grad">Email reset link</button>
    </form>

    <p style="font-size:12.5px; color:var(--text-meta); text-align:center; margin-top:20px;">
        <a href="{{ route('login') }}" wire:navigate style="color:var(--primary); font-weight:600; text-decoration:none;">← Back to sign in</a>
    </p>
</div>
