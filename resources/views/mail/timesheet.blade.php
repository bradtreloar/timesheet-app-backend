@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Timesheet submitted

@component('mail::table')
| | |
| :-- | :-- |
| __Employee__  | {{ $timesheet->user->name }} |
| __Submitted__ | {{ $timesheet->created_at->format("j F Y") }} |
@endcomponent

<br/>

## Shifts, Leave and Absences

@component('mail::table')
<table>
    <thead>
        <tr>
            <th style="text-align:left">Date</th>
            <th style="text-align:right">Start</th>
            <th style="text-align:right">End</th>
            <th style="text-align:right">Break</th>
            <th style="text-align:right">Hours</th>
        </tr>
    <thead>
    <tbody>
        @foreach ($timesheet->entries as $entry)
            @if ($entry->date->dayOfWeekIso <= 5)
                <tr>
                    <td style="text-align:left">
                        {{ $entry->date->format("D, j M Y") }}
                    </td>
                    @if (get_class($entry) == "App\Models\Shift")
                        <td style="text-align:right">
                            {{ $entry->start->format("H:i") }}
                        </td>
                        <td style="text-align:right">
                            {{ $entry->end->format("H:i") }}
                        </td>
                        <td style="text-align:right">
                            {{ $entry->break_duration }} min
                        </td>
                        <td style="text-align:right">
                            {{ $entry->hours }} hours
                        </td>
                    @elseif (get_class($entry) == "App\Models\Leave")
                        <td colspan="3" style="text-align:right">
                            {{ $entry->reasonLabel }}
                        <td>
                        <td style="text-align:right">
                            {{ $entry->hours }} hours
                        </td>
                    @else
                        <td colspan="4" style="text-align:right">
                            {{ $entry->reasonLabel }}
                        <td>
                    @endif
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="4" style="text-align:left">
                <strong>Total Weekday Shift Hours</strong>
            </td>
            <td style="text-align:right">
                {{ $timesheet->total_weekday_shift_hours }} hours
            </td>
        </tr>
        @if ($timesheet->total_leave_hours > 0)
            <tr>
                <td colspan="4" style="text-align:left">
                    <strong>Total Leave Hours</strong>
                </td>
                <td style="text-align:right">
                    {{ $timesheet->total_leave_hours }} hours
                </td>
            </tr>
        @endif
        @foreach ($timesheet->entries as $entry)
            @if ($entry->date->dayOfWeekIso > 5)
                <tr>
                    <td style="text-align:left">
                        {{ $entry->date->format("D, j M Y") }}
                    </td>
                    @if (get_class($entry) == "App\Models\Shift")
                        <td style="text-align:right">
                            {{ $entry->start->format("H:i") }}
                        </td>
                        <td style="text-align:right">
                            {{ $entry->end->format("H:i") }}
                        </td>
                        <td style="text-align:right">
                            {{ $entry->break_duration }} min
                        </td>
                        <td style="text-align:right">
                            {{ $entry->hours }} hours
                        </td>
                    @elseif (get_class($entry) == "App\Models\Leave")
                        <td colspan="3" style="text-align:right">
                            {{ $entry->reasonLabel }}
                        <td>
                        <td style="text-align:right">
                            {{ $entry->hours }} hours
                        </td>
                    @else
                        <td colspan="4" style="text-align:right">
                            {{ $entry->reasonLabel }}
                        <td>
                    @endif
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@endcomponent

## Comment

{{ $timesheet->comment ? $timesheet->comment : "<none>" }}

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent
