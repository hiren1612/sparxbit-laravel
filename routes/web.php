<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('bits', App\Livewire\Bits\Index::class)->name('bits.index');
    Route::get('bits/new', App\Livewire\Bits\Create::class)->name('bits.create');

    Route::post('logout', function () {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

require __DIR__.'/auth.php';
