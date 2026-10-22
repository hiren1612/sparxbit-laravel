<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'username', 'bio', 'avatar_url', 'timezone',
        'week_starts_on', 'theme', 'language', 'notifications_push_enabled',
        'default_reminder_time', 'notifications_weekly_summary', 'notifications_product_updates',
        'status', 'onboarding_completed_at', 'archived_at', 'auto_sync', 'referral_code',
        'referred_by_user_id', 'premium_tier', 'business_name', 'ad_tagline', 'ad_link', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime',
            'notifications_seen_at' => 'datetime',
            'archived_at' => 'datetime',
            'notifications_push_enabled' => 'boolean',
            'notifications_weekly_summary' => 'boolean',
            'notifications_product_updates' => 'boolean',
            'auto_sync' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    // Only admins can open the Filament panel at /admin.
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    public function bits(): HasMany
    {
        return $this->hasMany(Bit::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(PushToken::class);
    }

    public function webPushSubscriptions(): HasMany
    {
        return $this->hasMany(WebPushSubscription::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    public function referralsMade(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralsReceived(): HasMany
    {
        return $this->hasMany(Referral::class, 'referee_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }
}
