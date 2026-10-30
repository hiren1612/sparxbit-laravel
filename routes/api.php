<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BitController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReferralController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// --- Public ---
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Mobile passwordless + social sign-in.
Route::post('auth/magic/request', [MobileAuthController::class, 'magicRequest'])->middleware('throttle:3,1');
Route::post('auth/magic/verify', [MobileAuthController::class, 'magicVerify'])->middleware('throttle:10,1');
Route::post('auth/social', [MobileAuthController::class, 'social'])->middleware('throttle:10,1');

Route::get('health', function () {
    try {
        DB::select('select 1');
        $db = true;
    } catch (\Throwable $e) {
        $db = false;
    }

    return ['ok' => true, 'db' => $db, 'time' => now()->toISOString()];
});

// --- Authenticated (Sanctum token) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Push notification device tokens (Expo).
    Route::post('push-tokens', [App\Http\Controllers\Api\PushTokenController::class, 'store']);
    Route::delete('push-tokens', [App\Http\Controllers\Api\PushTokenController::class, 'destroy']);

    // Profile
    Route::get('me', [ProfileController::class, 'me']);
    Route::patch('me', [ProfileController::class, 'update']);
    Route::delete('me', [ProfileController::class, 'destroy']);
    Route::post('me/archive', [ProfileController::class, 'archive']);
    Route::post('me/reset', [ProfileController::class, 'reset']);
    Route::post('onboarding/finish', [ProfileController::class, 'finishOnboarding']);
    Route::post('onboarding/seed-bits', [OnboardingController::class, 'seedBits']);

    // Bits (literal routes before {id})
    Route::get('bits/all-changes', [BitController::class, 'allChanges']);
    Route::get('bits', [BitController::class, 'index']);
    Route::post('bits', [BitController::class, 'store']);
    Route::get('bits/{id}', [BitController::class, 'show']);
    Route::put('bits/{id}', [BitController::class, 'upsert']);
    Route::patch('bits/{id}', [BitController::class, 'update']);
    Route::delete('bits/{id}', [BitController::class, 'destroy']);
    Route::get('bits/{id}/changes', [BitController::class, 'changes']);

    // Entries
    Route::get('entries', [EntryController::class, 'index']);
    Route::put('entries', [EntryController::class, 'upsert']);
    Route::delete('entries', [EntryController::class, 'destroy']);

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::patch('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Catalog
    Route::get('goals', [CatalogController::class, 'goals']);
    Route::get('suggestions', [CatalogController::class, 'suggestions']);

    // Referrals / ads / feedback
    Route::get('referrals/mine', [ReferralController::class, 'mine']);
    Route::post('referrals/claim', [ReferralController::class, 'claim']);
    Route::get('ads/feed', [AdController::class, 'feed']);
    Route::post('feedback', [FeedbackController::class, 'store']);
    Route::post('achievements/sync', [AchievementController::class, 'sync']);

    // Admin-editable app copy (Filament → Settings), shared with the website.
    Route::get('app-content', fn () => [
        'aboutTagline' => \App\Models\Setting::get('about.tagline'),
        'aboutFooter' => \App\Models\Setting::get('about.footer'),
    ]);
});
