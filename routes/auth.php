<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // Passwordless magic-link sign-in.
    Route::get('auth/magic/{token}', [MagicLinkController::class, 'verify'])
        ->name('magic.verify');

    // Social sign-in (Google / Apple). Apple returns via a cross-site POST.
    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

// Outside "guest": the mobile app's in-app browser may share a signed-in
// website session, and the app sign-in must still run.
Route::match(['get', 'post'], 'auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');
Route::get('auth/{provider}/app', [SocialAuthController::class, 'appRedirect'])
    ->name('social.app');

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
