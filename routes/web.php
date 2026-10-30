<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Capture a referral code from the invite link (?ref=…) so it can be
    // attributed when the visitor signs up.
    if (request()->filled('ref')) {
        session(['referral_code' => request('ref')]);
    }

    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Public legal pages (linked from login + About).
// Magic link for the mobile app: a tiny page that bounces into the app
// (sparxbit://magic?token=…). It does NOT consume the token — the app does.
Route::get('auth/magic/app/{token}', fn (string $token) => view('auth.magic-app', ['token' => $token]))
    ->where('token', '[A-Za-z0-9]{20,128}')
    ->name('magic.app');

Route::get('terms', App\Livewire\Legal::class)->defaults('doc', 'terms')->name('terms');
Route::get('privacy', App\Livewire\Legal::class)->defaults('doc', 'privacy')->name('privacy');
Route::get('licenses', App\Livewire\Legal::class)->defaults('doc', 'licenses')->name('licenses');

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
    Route::get('notifications', App\Livewire\Notifications::class)->name('notifications.index');
    Route::get('profile', App\Livewire\Profile::class)->name('profile');
    Route::get('profile/edit', App\Livewire\Settings\EditProfile::class)->name('profile.edit');
    Route::get('profile/email', App\Livewire\Settings\Email::class)->name('profile.email');
    Route::get('profile/appearance', App\Livewire\Settings\Appearance::class)->name('profile.appearance');
    Route::get('profile/week-start', App\Livewire\Settings\WeekStart::class)->name('profile.weekstart');
    Route::get('profile/notifications', App\Livewire\Settings\Notifications::class)->name('profile.notifications');
    Route::get('profile/language', App\Livewire\Settings\Language::class)->name('profile.language');
    Route::get('profile/sync', App\Livewire\Settings\Sync::class)->name('profile.sync');
    Route::get('profile/monthly-report', App\Livewire\MonthlyReport::class)->name('profile.report');
    Route::get('profile/privacy', App\Livewire\Settings\Privacy::class)->name('profile.privacy');
    Route::get('help', App\Livewire\Settings\Help::class)->name('help');
    Route::get('profile/my-ad', App\Livewire\Settings\MyAd::class)->name('profile.myad');
    Route::get('profile/delete', App\Livewire\Settings\DeleteAccount::class)->name('profile.delete');
    Route::get('refer', App\Livewire\Refer::class)->name('refer');
    Route::get('categories', App\Livewire\Categories::class)->name('categories');
    Route::get('feedback', App\Livewire\Feedback::class)->name('feedback');
    Route::get('about', App\Livewire\About::class)->name('about');

    // Keep the user's timezone in sync with their browser so reminders fire
    // at their real local time (the column otherwise stays at the UTC default).
    Route::post('set-timezone', function (\Illuminate\Http\Request $request) {
        $tz = (string) $request->input('timezone');
        if (in_array($tz, timezone_identifiers_list(), true) && $request->user()->timezone !== $tz) {
            $request->user()->update(['timezone' => $tz]);
        }

        return response()->json(['ok' => true]);
    })->name('set-timezone');

    // Browser (Web Push) notification subscriptions.
    Route::post('web-push/subscribe', [App\Http\Controllers\WebPushController::class, 'subscribe'])->name('webpush.subscribe');
    Route::post('web-push/unsubscribe', [App\Http\Controllers\WebPushController::class, 'unsubscribe'])->name('webpush.unsubscribe');
    Route::post('web-push/test', [App\Http\Controllers\WebPushController::class, 'test'])->name('webpush.test');

    Route::post('logout', function () {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

require __DIR__.'/auth.php';
