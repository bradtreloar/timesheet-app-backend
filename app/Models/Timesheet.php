<?php

namespace App\Models;

use App\Events\TimesheetCompleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasFactory;

    const STATE_DRAFT = 'timesheet.draft';

    const STATE_COMPLETED = 'timesheet.completed';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => self::STATE_DRAFT,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
