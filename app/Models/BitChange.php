<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitChange extends Model
{
    use HasUuids;

    protected $table = 'bit_changes';
    public $timestamps = false; // only has changed_at

    protected $fillable = [
        'bit_id', 'user_id', 'field', 'from_val', 'to_val', 'changed_at',
    ];

    protected $casts = ['changed_at' => 'datetime'];

    public function bit(): BelongsTo
    {
        return $this->belongsTo(Bit::class);
    }
}
