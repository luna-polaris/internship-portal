<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_toggle_user_account_status_and_inactive_accounts_cannot_log_in(): void
    {
        $admin = User::create([
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'adminuser',
            'password' => 'secret123',
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        $user = User::create([
            'full_name' => 'Student User',
            'email' => 'student@example.com',
            'username' => 'studentuser',
            'password' => 'secret123',
            'role' => 'Student',
            'status' => 'Active',
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonFragment(['email' => 'student@example.com']);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $user->user_id . '/deactivate')
            ->assertOk()
            ->assertJsonPath('message', 'User deactivated.');

        $this->assertDatabaseHas('users', ['user_id' => $user->user_id, 'status' => 'Inactive']);

        $this->postJson('/api/login', [
            'email' => 'student@example.com',
            'password' => 'secret123',
        ])->assertStatus(403)
            ->assertJsonPath('message', 'Your account has been deactivated. Contact support.');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $user->user_id . '/activate')
            ->assertOk()
            ->assertJsonPath('message', 'User activated.');

        $this->assertDatabaseHas('users', ['user_id' => $user->user_id, 'status' => 'Active']);
    }
}
