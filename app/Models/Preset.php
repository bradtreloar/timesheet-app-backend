<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preset extends Model
{
    use HasFactory;

    protected $fillable = [
        'values',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
