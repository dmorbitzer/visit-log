<?php

namespace Tests\Feature\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_user(): void
    {
        $this->artisan('app:create-admin-user')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Username', 'admin')
            ->expectsQuestion('Email', 'admin@visitlog.test')
            ->expectsQuestion('Password', 'password')
            ->expectsOutput("Admin user 'admin' created successfully.")
            ->assertExitCode(0);

        $user = User::where('email', 'admin@visitlog.test')->first();

        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Admin, $user->role);
    }

    public function test_does_not_create_duplicate_admin(): void
    {
        User::factory()->admin()->create(['username' => 'admin']);

        $this->artisan('app:create-admin-user')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Username', 'admin')
            ->expectsQuestion('Email', 'admin@visitlog.test')
            ->expectsQuestion('Password', 'password')
            ->expectsOutput("User with username 'admin' already exists.")
            ->assertExitCode(0);

        $this->assertEquals(1, User::where('username', 'admin')->count());
    }
}
