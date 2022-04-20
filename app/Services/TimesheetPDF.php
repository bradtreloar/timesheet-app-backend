<?php

namespace App\Services;

use App\Models\Timesheet;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class TimesheetPDF
{
    /**
     * Creates the timesheet PDF if it doesn't exist, and returns the filename.
     */
    public static function create($timesheet)
    {
        $timestamp = $timesheet->created_at->getTimestamp();
        $username = $timesheet->user->snakecase_name;
        $filename = "timesheet_{$timestamp}_{$username}.pdf";
        $storage = Storage::disk('temporary');

        if ($storage->missing($filename)) {
            $html = View::make('pdf.timesheet', [
                'timesheet' => $timesheet,
            ]);
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $storage->put($filename, $dompdf->output());
        }

        return $storage->path($filename);
    }
}
