<?php

namespace Tests\Unit\Services;

use App\Models\Timesheet;
use App\Models\User;
use App\Services\TimesheetPDF;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase as TestCase;

/**
 * @coversDefaultClass \App\Services\TimesheetPDF
 */
class TimesheetPDFTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Writes PDF file when file is missing.
     *
     * @covers ::create
     */
    public function testWritesPdfFileWhenFileMissing()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
        $timestamp = $timesheet->created_at->getTimestamp();
        $username = $timesheet->user->snakecase_name;
        $html = $this->faker->randomHtml();
        $filename = "timesheet_{$timestamp}_{$username}.pdf";
        Storage::spy();
        Storage::shouldReceive('disk')->with('temporary')->andReturnSelf();
        Storage::shouldReceive('missing')->andReturn(true);
        Storage::shouldReceive('put');
        Storage::shouldReceive('path')->andReturn($filename);
        View::spy();
        View::shouldReceive('make')->with('pdf.timesheet', [
            'timesheet' => $timesheet,
        ])->andReturn($html);

        $pdfWriter = new TimesheetPDF();
        $result = $pdfWriter->create($timesheet);

        $this->assertEquals($filename, $result);
        Storage::shouldHaveReceived('disk')->with('temporary');
        Storage::shouldHaveReceived('missing')->with($filename);
        View::shouldHaveReceived('make')->with('pdf.timesheet', [
            'timesheet' => $timesheet,
        ]);
        Storage::shouldReceived('put');
    }

    /**
     * Skips writing PDF file when file is present.
     *
     * @covers ::create
     */
    public function testSkipsWritingPdfFileWhenFilePresent()
    {
        $user = User::factory()->create();
        $timesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
        ]);
        $timestamp = $timesheet->created_at->getTimestamp();
        $username = $timesheet->user->snakecase_name;
        $html = $this->faker->randomHtml();
        $filename = "timesheet_{$timestamp}_{$username}.pdf";
        Storage::spy();
        View::spy();
        Storage::shouldReceive('disk')->with('temporary')->andReturnSelf();
        Storage::shouldReceive('missing')->andReturn(false);
        Storage::shouldNotReceive('put');
        View::shouldNotReceive('make');
        Storage::shouldReceive('path')->andReturn($filename);

        $pdfWriter = new TimesheetPDF();
        $result = $pdfWriter->create($timesheet);

        $this->assertEquals($filename, $result);
        Storage::shouldHaveReceived('disk')->with('temporary');
        Storage::shouldHaveReceived('missing')->with($filename);
        Storage::shouldHaveReceived('path');
    }
}
