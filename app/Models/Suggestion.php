<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suggestion extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key', 'goal_key', 'title', 'default_why', 'default_goal_per_week', 'icon_key',
    ];

    protected $casts = ['default_goal_per_week' => 'integer'];
}
