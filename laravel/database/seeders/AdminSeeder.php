<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates the initial Admin account. Admins are never created through the
 * public register form, so this is the only way to get one into the system.
 * Credentials come from .env so nothing sensitive is hardcoded in source.
 */
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
