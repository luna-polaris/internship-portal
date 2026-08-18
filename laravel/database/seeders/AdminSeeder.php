<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Only way to create an Admin — never done via public register; credentials come from .env. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $email = env('ADMIN_EMAIL', 'admin@internhub.test');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            $this->command->error('Set ADMIN_PASSWORD in .env before running this seeder.');

            return;
        }

        if (User::where('username', $username)->exists()) {
            $this->command->info("Admin '{$username}' already exists, skipping.");

            return;
        }

        $user = User::create([
            'full_name' => 'System Administrator',
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        Admin::create([
            'user_id' => $user->user_id,
            'admin_level' => 'Super Admin',
        ]);

        $this->command->info("Admin '{$username}' created.");
    }
}
