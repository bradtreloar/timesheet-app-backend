<?php

namespace Tests\API;

use App\Models\Absence;
use App\Models\Leave;
use App\Models\Preset;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class JsonApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $defaultHeaders = [
        "Origin" => "localhost",
    ];

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Call the given URI with a JSON:API request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Testing\TestResponse
     */
    public function jsonApi($method, $uri, array $data = [], array $headers = [])
    {
        $headers = array_merge([
            "Content-Type" => "application/vnd.api+json",
            "Accept" => "application/vnd.api+json",
        ], $headers);

        return parent::json($method, $uri, $data, $headers);
    }

    public function testRejectUnauthenticated()
    {
        $this->seed();
        $response = $this->jsonApi("GET", "/users");
        $response->assertUnauthorized();
    }

    public function testFetchUser()
    {
        $this->seed();
        $user = User::first();
        $response = $this->actingAs($user)
            ->jsonApi('GET', "/users/{$user->id}");
        $response->assertJson([
            'data' => $this->makeUserResource($user),
        ]);
    }

    public function testFetchUserIncludingDefaultPreset()
    {
        $this->seed();
        $user = User::first();
        $response = $this->actingAs($user)
            ->jsonApi('GET', "/users/{$user->id}?include=default_preset");
        $response->assertSuccessful();
        $response->assertJson([
            'data' => $this->makeUserResource($user),
        ]);
        $response->assertJson([
            'included' => [
                [
                    'type' => 'presets',
                    'id' => $user->defaultPreset->id,
                ]
            ],
        ]);
    }

    public function testDenyFetchUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $response = $this->actingAs($user)
            ->jsonApi('GET', "/users/{$other_user->id}");
        $response->assertForbidden();
    }

    public function testFetchAllUsers()
    {
        $this->seed();
        $user = User::factory()->create([
            'is_admin' => true,
        ]);
        $this->assertDatabaseCount('users', 3);
        $response = $this->actingAs($user)
            ->jsonApi('GET', "/users");
        $response->assertJson([
            "data" => [
                ["type" => "users"],
                ["type" => "users"],
                ["type" => "users"],
            ],
        ]);
    }

    public function testDenyFetchAllUsers()
    {
        $this->seed();
        $user = User::factory()->create([
            'is_admin' => false,
        ]);
        $this->assertDatabaseCount('users', 3);
        $response = $this->actingAs($user)
            ->jsonApi('GET', "/users");
        $response->assertForbidden();
    }

    public function testCreateUser()
    {
        $this->seed();
        $admin_user = User::factory()->create([
            'is_admin' => true,
        ]);
        $this->assertDatabaseCount('users', 3);
        $user = User::factory()->make();
        $request_data = [
            "data" => $this->makeUserResource($user),
        ];
        $response = $this->actingAs($admin_user)
            ->jsonApi("POST", "/users", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "users",
                "id" => "4",
            ]
        ]);

        $this->assertDatabaseCount('users', 4);
    }

    public function testDenyCreateUser()
    {
        $this->seed();
        $admin_user = User::factory()->create([
            'is_admin' => false,
        ]);
        $this->assertDatabaseCount('users', 3);
        $user = User::factory()->make();
        $request_data = [
            "data" => $this->makeUserResource($user),
        ];
        $response = $this->actingAs($admin_user)
            ->jsonApi("POST", "/users", $request_data);
        $response->assertForbidden();
        $this->assertDatabaseCount('users', 3);
    }

    public function testUpdateUser()
    {
        $this->seed();
        $user = User::find(1);
        $name = $this->faker->name();
        $user->name = $name;

        $request_data = [
            "data" => $this->makeUserResource($user),
        ];

        $response = $this->actingAs($user)
            ->jsonApi("PATCH", "/users/{$user->id}", $request_data);
        $response->assertSuccessful();
        $response->assertJson($request_data);

        $updated_user = User::find(1);
        $this->assertEquals($name, $updated_user->name);
    }

    public function testDenyUpdateOtherUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $original_name = $other_user->name;
        $other_user->name = $this->faker->name();
        $request_data = [
            "data" => $this->makeUserResource($other_user),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("PATCH", "/users/{$other_user->id}", $request_data);
        $response->assertForbidden();
        $updated_user = User::find(2);
        $this->assertEquals($original_name, $updated_user->name);
    }

    public function testDenyDeleteUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $response = $this->actingAs($user)
            ->jsonApi("DELETE", "/users/{$other_user->id}");
        $response->assertForbidden();
    }

    public function testIgnoreChangeUserPassword()
    {
        $this->seed();
        $user = User::find(1);
        $old_password = $user->password;
        $new_password = Hash::make(Str::random(40));
        $user_data = $this->makeUserResource($user);
        $user_data['attributes']['password'] = $new_password;
        $request_data = [
            "data" => $user_data,
        ];
        $response = $this->actingAs($user)
            ->jsonApi("PATCH", "/users/{$user->id}", $request_data);
        $response->assertSuccessful();
        $updated_user = User::find(1);
        $this->assertEquals($old_password, $updated_user->password);
        $response->assertJsonMissing([
            "data" => [
                "attributes" => [
                    "password" => $new_password,
                ],
            ]
        ]);
    }

    public function testFetchAllSettings()
    {
        $this->seed();
        $user = User::find(1);
        $user->is_admin = true;
        $user->save();
        $settings = Setting::all();
        $response = $this->actingAs($user)->jsonApi("GET", "/settings");
        $response->assertSuccessful();
        $this->assertCount(1, $response->json("data"));
        foreach ($settings as $index => $setting) {
            $response->assertJson([
                'data' => [
                    $index => [
                        "attributes" => [
                            "name" => $setting->name,
                            "value" => $setting->value,
                        ]
                    ]
                ]
            ]);
        }
    }

    public function testUpdateSetting()
    {
        $this->seed();
        $user = User::find(1);
        $user->is_admin = true;
        $user->save();
        $setting = Setting::where("name", "timesheetRecipients")->first();
        $sid = $setting->id;
        $newValue = $this->faker->email();
        $request_data = [
            "data" => [
                "id" => (string) $sid,
                "type" => "settings",
                "attributes" => [
                    "value" => $newValue,
                ],
            ],
        ];
        $response = $this->actingAs($user)
            ->jsonApi("PATCH", "/settings/{$setting->id}", $request_data);
        $response->assertSuccessful();
        $setting = Setting::find($sid);
        $this->assertEquals($newValue, $setting->value);
    }

    public function testFetchTimesheet()
    {
        $this->seed();
        $timesheet = Timesheet::first();
        $user = $timesheet->user;
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/timesheets/{$timesheet->id}");
        $response->assertSuccessful();
        $response->assertJson([
            "data" => [
                "id" => "1",
            ],
        ]);
    }

    public function testFetchTimesheetWithShifts()
    {
        $this->seed();
        $timesheet = Timesheet::first();
        $user = $timesheet->user;
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/timesheets/{$timesheet->id}?include=shifts");
        $response->assertSuccessful();
        $id = $response->json("data.id");
        $this->assertEquals("1", $id);
        $shifts = $response->json("included");
        $this->assertIsArray($shifts);
        $this->assertEquals(1, count($shifts));
    }

    public function testDenyFetchAllTimesheets()
    {
        $this->seed();
        $user = User::find(1);
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/timesheets");
        $response->assertForbidden();
    }

    public function testDenyFetchTimesheetForOtherUser()
    {
        $this->seed();
        $timesheet = Timesheet::find(1);
        $user = $timesheet->user;
        // Try to retrieve timesheet that doesn"t belong to user.
        $timesheet = Timesheet::find(2);
        $this->assertEquals(2, $timesheet->id);
        $response = $this->actingAs($user)
            ->get("/timesheets/{$timesheet->id}");
        $response->assertForbidden();
    }

    public function testFetchAllTimesheetsForUser()
    {
        $this->seed();
        $user = User::first();
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/users/{$user->id}/timesheets");
        $response->assertSuccessful();
        $data = $response->json("data");
        $this->assertEquals(1, count($data));
    }

    public function testFetchUpdatedTimesheetsForUser()
    {
        $user = User::factory()->create();
        $date = Carbon::create(2001, 5, 21, 12, 0, 0);
        $oldDate = (new Carbon($date))->subWeeks(1);
        $newDate = (new Carbon($date))->addWeeks(1);
        $oldTimesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'created_at' => $oldDate,
            'updated_at' => $oldDate,
        ]);
        $newTimesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'created_at' => $newDate,
            'updated_at' => $newDate,
        ]);
        $updatedTimesheet = Timesheet::factory()->create([
            'user_id' => $user->id,
            'created_at' => $oldDate,
            'updated_at' => $newDate,
        ]);

        $queryString = 'filter[updated-after]=' . $date->toISOString();
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/users/{$user->id}/timesheets?$queryString");

        $response->assertSuccessful();
        $data = $response->json("data");
        $this->assertEquals(2, count($data));
    }

    public function testDenyFetchAllTimesheetsForOtherUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/users/{$other_user->id}/timesheets");
        $response->assertForbidden();
    }

    public function testCreateTimesheet()
    {
        $this->seed();
        $user = User::first();
        $timesheet = Timesheet::factory()->make([
            'user_id' => $user->id,
        ]);
        $request_data = [
            "data" => $this->makeTimesheetResource($timesheet),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/timesheets", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "timesheets",
                "id" => "3",
            ]
        ]);
        $this->assertDatabaseCount("timesheets", 3);
    }

    public function testDenyCreateTimesheetForOtherUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $timesheet = Timesheet::factory()->make([
            'user_id' => $other_user->id,
        ]);
        $request_data = [
            "data" => $this->makeTimesheetResource($timesheet),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/timesheets", $request_data);
        $response->assertForbidden();
        $this->assertDatabaseCount("timesheets", 2);
    }

    public function testDeleteTimesheet()
    {
        $this->seed();
        $timesheet = Timesheet::first();
        $user = $timesheet->user;
        $response = $this->actingAs($user)
            ->jsonApi("DELETE", "/timesheets/{$timesheet->id}");
        $response->assertNoContent();
        $this->assertDatabaseCount("timesheets", 1);
    }

    public function testDenyDeleteTimesheetForOtherUser()
    {
        $this->seed();
        $timesheet = Timesheet::find(1);
        $other_timesheet = Timesheet::find(2);
        $user = $timesheet->user;
        $response = $this->actingAs($user)
            ->jsonApi("DELETE", "/timesheets/{$other_timesheet->id}");
        $response->assertForbidden();
        $this->assertDatabaseCount("timesheets", 2);
    }

    public function testCreateShift()
    {
        $this->seed();
        $timesheet = Timesheet::find(1);
        $user = $timesheet->user;
        $shift = Shift::factory()->make([
            'timesheet_id' => $timesheet->id,
        ]);
        $request_data = [
            "data" => $this->makeShiftResource($shift),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/shifts", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "shifts",
                "id" => "3",
            ]
        ]);
        $this->assertDatabaseCount("shifts", 3);
    }

    public function testCreateAbsence()
    {
        $this->seed();
        $timesheet = Timesheet::find(1);
        $user = $timesheet->user;
        $absence = Absence::factory()->make([
            'timesheet_id' => $timesheet->id,
        ]);
        $request_data = [
            "data" => $this->makeAbsenceResource($absence),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/absences", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "absences",
                "id" => "3",
            ]
        ]);
        $this->assertDatabaseCount("absences", 3);
    }

    public function testCreateLeave()
    {
        $this->seed();
        $timesheet = Timesheet::find(1);
        $user = $timesheet->user;
        $leave = Leave::factory()->make([
            'timesheet_id' => $timesheet->id,
        ]);
        $request_data = [
            "data" => $this->makeLeaveResource($leave),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/leaves", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "leaves",
                "id" => "3",
            ]
        ]);
        $this->assertDatabaseCount("leaves", 3);
    }

    public function testFetchPreset()
    {
        $this->seed();
        $preset = Preset::first();
        $user = $preset->user;
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/presets/{$preset->id}");
        $response->assertSuccessful();
        $response->assertJson([
            "data" => [
                "type" => "presets",
                "id" => $preset->id,
            ],
        ]);
    }

    public function testDenyFetchAllPresets()
    {
        $this->seed();
        $user = User::find(1);
        $response = $this->actingAs($user)
            ->jsonApi("GET", "/presets");
        $response->assertForbidden();
    }

    public function testDenyFetchPresetForOtherUser()
    {
        $this->seed();
        $preset = Preset::find(1);
        $user = $preset->user;
        // Try to retrieve preset that doesn"t belong to user.
        $preset = Preset::find(2);
        $this->assertEquals(2, $preset->id);
        $response = $this->actingAs($user)
            ->get("/presets/{$preset->id}");
        $response->assertForbidden();
    }

    public function testCreatePreset()
    {
        $this->seed();
        $user = User::first();
        $preset = Preset::factory()->make([
            'user_id' => $user->id,
        ]);
        $request_data = [
            "data" => $this->makePresetResource($preset),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/presets", $request_data);
        $response->assertCreated();
        $response->assertJson([
            "data" => [
                "type" => "presets",
                "id" => "3",
            ]
        ]);
        $this->assertDatabaseCount("presets", 3);
    }

    public function testDenyCreatePresetForOtherUser()
    {
        $this->seed();
        $user = User::find(1);
        $other_user = User::find(2);
        $preset = Preset::factory()->make([
            'user_id' => $other_user->id,
        ]);
        $request_data = [
            "data" => $this->makePresetResource($preset),
        ];
        $response = $this->actingAs($user)
            ->jsonApi("POST", "/presets", $request_data);
        $response->assertForbidden();
        $this->assertDatabaseCount("presets", 2);
    }

    public function testDeletePreset()
    {
        $this->seed();
        $preset = Preset::first();
        $user = $preset->user;
        $response = $this->actingAs($user)
            ->jsonApi("DELETE", "/presets/{$preset->id}");
        $response->assertNoContent();
        $this->assertDatabaseCount("presets", 1);
    }

    public function testDenyDeletePresetForOtherUser()
    {
        $this->seed();
        $preset = Preset::find(1);
        $other_preset = Preset::find(2);
        $user = $preset->user;
        $response = $this->actingAs($user)
            ->jsonApi("DELETE", "/presets/{$other_preset->id}");
        $response->assertForbidden();
        $this->assertDatabaseCount("presets", 2);
    }

    /**
     * Creates a timesheet resource.
     *
     * @param \App\Models\Timesheet $timesheet
     *   The timesheet.
     *
     * @return array
     *   The timesheet resource data.
     */
    protected function makeTimesheetResource(Timesheet $timesheet): array
    {
        $resource = [
            "type" => "timesheets",
            "attributes" => [
                "comment" => $timesheet->comment,
            ],
            "relationships" => [
                "user" => [
                    "data" => [
                        "type" => "users",
                        "id" => "{$timesheet->user->id}",
                    ]
                ],
            ],
        ];

        if ($timesheet->exists) {
            $resource['id'] = (string) $timesheet->id;
        }

        return $resource;
    }

    /**
     * Creates a shift resource.
     *
     * @param \App\Models\Shift $shift
     *   The shift.
     *
     * @return array
     *   The shift resource data.
     */
    protected function makeShiftResource(Shift $shift): array
    {
        $resource = [
            "type" => "shifts",
            "attributes" => [
                "start" => $shift->start->toISO8601String(),
                "end" => $shift->end->toISO8601String(),
                "breakDuration" => $shift->break_duration,
            ],
            "relationships" => [
                "timesheet" => [
                    "data" => [
                        "type" => "timesheets",
                        "id" => "{$shift->timesheet->id}",
                    ]
                ],
            ],
        ];

        if ($shift->exists) {
            $resource['id'] = (string) $shift->id;
        }

        return $resource;
    }

    /**
     * Creates an absence resource.
     *
     * @param \App\Models\Absence $absence
     *   The absence.
     *
     * @return array
     *   The absence resource data.
     */
    protected function makeAbsenceResource(Absence $absence): array
    {
        $resource = [
            "type" => "absences",
            "attributes" => [
                "date" => $absence->date->toISO8601String(),
                "reason" => $absence->reason,
            ],
            "relationships" => [
                "timesheet" => [
                    "data" => [
                        "type" => "timesheets",
                        "id" => "{$absence->timesheet->id}",
                    ]
                ],
            ],
        ];

        if ($absence->exists) {
            $resource['id'] = (string) $absence->id;
        }

        return $resource;
    }

    /**
     * Creates a leave resource.
     *
     * @param \App\Models\Leave $leave
     *   The leave.
     *
     * @return array
     *   The leave resource data.
     */
    protected function makeLeaveResource(Leave $leave): array
    {
        $resource = [
            "type" => "leaves",
            "attributes" => [
                "date" => $leave->date->toISO8601String(),
                "hours" => $leave->hours,
                "reason" => $leave->reason,
            ],
            "relationships" => [
                "timesheet" => [
                    "data" => [
                        "type" => "timesheets",
                        "id" => "{$leave->timesheet->id}",
                    ]
                ],
            ],
        ];

        if ($leave->exists) {
            $resource['id'] = (string) $leave->id;
        }

        return $resource;
    }

    /**
     * Creates a preset resource.
     *
     * @param \App\Models\Preset $preset
     *   The preset.
     *
     * @return array
     *   The preset resource data.
     */
    protected function makePresetResource(Preset $preset): array
    {
        $resource = [
            "type" => "presets",
            "attributes" => [
                "values" => $preset->values,
            ],
            "relationships" => [
                "user" => [
                    "data" => [
                        "type" => "users",
                        "id" => "{$preset->user->id}",
                    ]
                ],
            ],
        ];

        if ($preset->exists) {
            $resource['id'] = (string) $preset->id;
        }

        return $resource;
    }

    /**
     * Creates a user resource.
     *
     * @param \App\Models\User $user
     *   The user.
     *
     * @return array
     *   The user resource data.
     */
    protected function makeUserResource(User $user)
    {
        $resource = [
            "type" => "users",
            "attributes" => [
                "name" => $user->name,
                "email" => $user->email,
                "phoneNumber" => $user->phone_number,
                "acceptsReminders" => $user->accepts_reminders,
                "isAdmin" => $user->is_admin,
            ],
        ];

        if ($user->exists) {
            $resource['id'] = (string) $user->id;
        }

        return $resource;
    }
}
