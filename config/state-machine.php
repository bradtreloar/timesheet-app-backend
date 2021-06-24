<?php

use App\Models\Timesheet;
use App\Services\TimesheetEventDispatcher;
use Carbon\Carbon;
use Sebdesign\SM\Event\TransitionEvent;

return [
    'timesheetState' => [
        'class' => App\Models\Timesheet::class,
        'property_path' => 'state',
        'metadata' => [
            'title' => 'Timesheet State',
        ],
        'states' => [
            Timesheet::STATE_DRAFT,
            Timesheet::STATE_COMPLETED,
        ],
        'transitions' => [
            'complete' => [
                'from' => [Timesheet::STATE_DRAFT],
                'to' => Timesheet::STATE_COMPLETED,
            ],
        ],
        'callbacks' => [
            'before' => [
                'on_complete' => [
                    'on' => 'complete',
                    'do' => function (Timesheet $timesheet) {
                        $timesheet->submitted_at = Carbon::now();
                        $timesheet->save();
                    },
                    'args' => ['object'],
                ],
            ],
            'after' => [
                'on_complete' => [
                    'on' => 'complete',
                    'do' => [
                        TimesheetEventDispatcher::class,
                        'dispatchCompletedEvent'
                    ],
                ],
            ],
        ],
    ],
];
