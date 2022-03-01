<?php

namespace App\Models;

use App\Events\TimesheetCompleted;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasFactory;

    const STATE_DRAFT = 'timesheet.draft';

    const STATE_COMPLETED = 'timesheet.completed';

    protected $fillable = [
        'comment',
    ];

    protected $appends = [
        'total_weekday_shift_hours',
        'total_leave_hours',
        'entries',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'state' => self::STATE_DRAFT,
        'submitted_at' => null,
    ];

    public function getTotalWeekdayShiftHoursAttribute()
    {
        $total_weekday_shift_hours = 0;
        $shifts = $this->shifts()->get();
        foreach ($shifts as $shift) {
            if ($shift->start->dayOfWeekIso <= 5) {
                $total_weekday_shift_hours += $shift->hours;
            }
        }
        return number_format($total_weekday_shift_hours, 2);
    }

    public function getTotalLeaveHoursAttribute()
    {
        $total_leave_hours = 0;
        $leaves = $this->leaves()->get();
        foreach ($leaves as $leave) {
            $total_leave_hours += $leave->hours;
        }
        return number_format($total_leave_hours, 2);
    }

    public function getEntriesAttribute()
    {
        $shifts = $this->shifts->sortBy('start');
        $absences = $this->absences->sortBy('date');
        $leaves = $this->leaves->sortBy('date');
        $entries = [];
        array_push($entries, ...$shifts, ...$absences, ...$leaves);
        usort($entries, function ($a, $b) {
            return $b->date->diffInDays($a->date, false);
        });
        return $entries;
    }

    public function scopeUpdatedAfter(Builder $query, string $cutoffDate)
    {
        return $query->where('updated_at', '>', Carbon::parse($cutoffDate));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
