<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bit extends Model
{
    use HasUuids;

    // 'id' is fillable so the offline-first mobile app can supply its own UUID.
    protected $fillable = [
        'id', 'user_id', 'title', 'why', 'description', 'goal_per_week',
        'start_date', 'end_date', 'category_id', 'goal_id', 'icon_key',
        'reminder_enabled', 'reminder_time', 'reminder_days', 'is_system', 'status',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'reminder_days' => 'array',
        'reminder_enabled' => 'boolean',
        'is_system' => 'boolean',
        'goal_per_week' => 'integer',
    ];

    // Turning on a reminder for a Bit (web or API) also switches on the user's
    // reminder delivery, so the reminder they just set actually arrives.
    protected static function booted(): void
    {
        static::saved(function (Bit $bit) {
            if ($bit->reminder_enabled && ! $bit->is_system) {
                User::whereKey($bit->user_id)
                    ->where('notifications_push_enabled', false)
                    ->update(['notifications_push_enabled' => true]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(BitChange::class);
    }
}
