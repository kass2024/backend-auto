<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminUser extends Command
{
    protected $signature = 'neamee:ensure-admin
                            {--email=admin@neamee-autotechsolutions.com : Admin email}
                            {--password=password : Admin password}';

    protected $description = 'Create or reset the NEAMEE admin user (run on cPanel after migrate)';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin User',
                'password' => Hash::make($password),
                'role' => 'admin',
                'phone' => '+1 (567) 329-9231',
            ]
        );

        $this->info("Admin ready: {$user->email}");
        $this->line('Password: '.$password);

        return self::SUCCESS;
    }
}
