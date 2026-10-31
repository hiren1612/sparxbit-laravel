<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['user_id', 'key', 'type', 'title', 'body', 'earned_at', 'seen_at'];

    protected $casts = ['earned_at' => 'datetime', 'seen_at' => 'datetime'];
}
