<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {email} {name} {--admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $user = new User([
                "name" => $this->argument("name"),
                "email" => $this->argument("email"),
                'is_admin' => $this->option("admin"),
            ]);
            $user->password = Hash::make(Str::random(40));
            $user->markEmailAsVerified();
            $user->save();
            return 0;
        } catch (QueryException $ex) {
            print("Unable to create user.\n");
            return 1;
        }
    }
}
