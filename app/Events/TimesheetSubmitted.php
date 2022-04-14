<?php

namespace App\Events;

use App\Models\Timesheet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimesheetSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Timesheet
     */
    public $timesheet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }
}
