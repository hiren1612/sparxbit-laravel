<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasUuids;

    protected $table = 'feedback';

    protected $fillable = ['user_id', 'type', 'message'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
