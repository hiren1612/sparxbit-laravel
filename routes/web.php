<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware(['auth'])->group(function () {
    Route::get('welcome', App\Livewire\Onboarding::class)->name('welcome');
    Route::get('dashboard', App\Livewire\Dashboard::class)->name('dashboard');
    Route::get('bits', App\Livewire\Bits\Index::class)->name('bits.index');
    Route::get('bits/archive', App\Livewire\Bits\Archive::class)->name('bits.archive');
    Route::get('bits/new', App\Livewire\Bits\Create::class)->name('bits.create');
    Route::get('bits/{bit}', App\Livewire\Bits\Show::class)->name('bits.show');
    Route::get('bits/{bit}/edit', App\Livewire\Bits\Edit::class)->name('bits.edit');
    Route::get('calendar', App\Livewire\Calendar::class)->name('calendar');
    Route::get('stats', App\Livewire\Stats::class)->name('stats');
    Route::get('profile', App\Livewire\Profile::class)->name('profile');
    Route::get('profile/edit', App\Livewire\Settings\EditProfile::class)->name('profile.edit');
    Route::get('profile/email', App\Livewire\Settings\Email::class)->name('profile.email');
    Route::get('profile/appearance', App\Livewire\Settings\Appearance::class)->name('profile.appearance');
    Route::get('profile/week-start', App\Livewire\Settings\WeekStart::class)->name('profile.weekstart');
    Route::get('profile/notifications', App\Livewire\Settings\Notifications::class)->name('profile.notifications');
    Route::get('profile/privacy', App\Livewire\Settings\Privacy::class)->name('profile.privacy');
    Route::get('profile/my-ad', App\Livewire\Settings\MyAd::class)->name('profile.myad');
    Route::get('profile/delete', App\Livewire\Settings\DeleteAccount::class)->name('profile.delete');
    Route::get('refer', App\Livewire\Refer::class)->name('refer');
    Route::get('categories', App\Livewire\Categories::class)->name('categories');
    Route::get('feedback', App\Livewire\Feedback::class)->name('feedback');
    Route::get('about', App\Livewire\About::class)->name('about');

    Route::post('logout', function () {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

require __DIR__.'/auth.php';
