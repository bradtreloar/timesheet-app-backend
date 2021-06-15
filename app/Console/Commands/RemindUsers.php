<?php

namespace App\Console\Commands;

use App\Events\TimesheetDue;
use App\Models\Absence;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class RemindUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to users who haven\'t submitted a timesheet for the current week.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            if (!$this->userHasCurrentTimesheet($user)) {
                Event::dispatch(new TimesheetDue($user));
            }
        }
    }

    /**
     * Check every timesheet for a shift or absence set in the future.
     */
    public function userHasCurrentTimesheet($user)
    {
        $timesheets = Timesheet::where([
            'user_id' => $user->id,
        ])->get();
        $start_of_timesheet_week = static::getStartOfCurrentTimesheetWeek();
        if ($timesheets) {
            foreach ($timesheets as $timesheet) {
                /** @var (\App\Models\Absence|\App\Models\Shift)[] $entries */
                $entries = $timesheet->shifts_and_absences;
                foreach ($entries as $entry) {
                    $diff = $start_of_timesheet_week->diffInDays($entry->date, false);
                    if ($diff >= 0 && $diff < 7) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get the start of the timesheet week.
     */
    public static function getStartOfCurrentTimesheetWeek()
    {
        $today = Carbon::now()->startOfDay();
        $start_of_timesheet_week = Carbon::now()->startOfWeek();
        if ($start_of_timesheet_week->diffInDays($today) == 0) {
            $start_of_timesheet_week = $start_of_timesheet_week->subDays(7);
        }
        return $start_of_timesheet_week;
    }
}
