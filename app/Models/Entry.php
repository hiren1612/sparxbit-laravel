<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use HasUuids;

    protected $fillable = [
        'id', 'bit_id', 'user_id', 'date', 'status', 'source', 'completed_at',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function bit(): BelongsTo
    {
        return $this->belongsTo(Bit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
