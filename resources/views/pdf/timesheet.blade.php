<html>

<head>
<style>
body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    border-top: 1px dotted #000;
    border-bottom: 1px dotted #000;
    padding: 0.5rem;
    text-align: left;
    vertical-align: top;
}
th {
    white-space: nowrap;
}
</style>
</head>

<body>
    <div>
        <p>Allbiz Supplies</p>
        <h1>Timesheet</h1>
    </div>
    <div>
        <table>
            <tbody>
                <tr>
                    <th>
                        Employee
                    </th>
                    <td style="width: 100%;">
                        {{ $timesheet->user->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        Submitted
                    </th>
                    <td style="width: 100%;">
                        {{ $timesheet->created_at->format("j F Y") }}
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Shifts, Leave and Absences</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:100%">
                        Date
                    </th>
                    <th style="text-align:right;white-space:nowrap">
                        Start
                    </th>
                    <th style="text-align:right;white-space:nowrap">
                        End
                    </th>
                    <th style="text-align:right;white-space:nowrap">
                        Break
                    </th>
                    <th style="text-align:right;white-space:nowrap">
                        Hours
                    </th>
                </tr>
            </thead>
            <tbody style="text-align: left;vertical-align:top;">
                @foreach ($timesheet->entries as $entry)
                    @if ($entry->date->dayOfWeekIso <= 5)
                        <tr>
                            <td style="width:100%">
                                {{ $entry->date->format("D, d-m-Y") }}
                            </td>
                            @if (get_class($entry) == "App\Models\Shift")
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->start->format("H:i") }}
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->end->format("H:i") }}
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->break_duration }} min
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->hours }} hours
                                </td>
                            @elseif (get_class($entry) == "App\Models\Leave")
                                <td colspan="3" style="text-align:right;white-space:nowrap">
                                    {{ $entry->reasonLabel }}
                                <td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->hours }} hours
                                </td>
                            @else
                                <td colspan="4" style="text-align:right;white-space:nowrap">
                                    {{ $entry->reasonLabel }}
                                </td>
                            @endif
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td colspan="4" style="text-align:right;white-space:nowrap">
                        <strong>Total Weekday Shift Hours</strong>
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        {{ $timesheet->total_weekday_shift_hours }} hours
                    </td>
                </tr>
                @if ($timesheet->total_leave_hours > 0)
                    <tr>
                        <td colspan="4" style="text-align:right;white-space:nowrap">
                            <strong>Total Leave Hours</strong>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            {{ $timesheet->total_leave_hours }} hours
                        </td>
                    </tr>
                @endif
                @foreach ($timesheet->entries as $entry)
                    @if ($entry->date->dayOfWeekIso > 5)
                        <tr>
                            <td style="width:100%">
                                {{ $entry->date->format("D, d-m-Y") }}
                            </td>
                            @if (get_class($entry) == "App\Models\Shift")
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->start->format("H:i") }}
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->end->format("H:i") }}
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->break_duration }} min
                                </td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->hours }} hours
                                </td>
                            @elseif (get_class($entry) == "App\Models\Leave")
                                <td colspan="3" style="text-align:right;white-space:nowrap">
                                    {{ $entry->reasonLabel }}
                                <td>
                                <td style="text-align:right;white-space:nowrap">
                                    {{ $entry->hours }} hours
                                </td>
                            @else
                                <td colspan="4" style="text-align:right;white-space:nowrap">
                                    {{ $entry->reasonLabel }}
                                </td>
                            @endif
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <h2>Comment</h2>
        <p>{{ $timesheet->comment ? $timesheet->comment : "none" }}</p>
    </div>
</body>

</html>

