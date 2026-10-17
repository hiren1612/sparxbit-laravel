<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'user_id', 'key', 'label', 'icon_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bits(): HasMany
    {
        return $this->hasMany(Bit::class);
    }
}
