<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-admin-user')]
#[Description('Create a new admin user')]
class CreateAdminUser extends Command
{
    public function handle(): void
    {
        $name = $this->ask('Name');
        $username = $this->ask('Username');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRole::Admin,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info("Admin user '{$username}' created successfully.");
        } else {
            $this->warn("User with username '{$username}' already exists.");
        }
    }
}
