<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'type', 'hours',
    ];

    protected $appends = [
        'typeLabel'
    ];

    protected $dates = [
        'date',
    ];

    public function getTypeLabelAttribute()
    {
        return [
            "absent:sick-day" => "Rostered but absent: sick day",
            "annual-leave" => "Annual leave",
            "long-service" => "Long service leave",
            "public-holiday" => "Public holiday",
        ][$this->reason];
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }
}
