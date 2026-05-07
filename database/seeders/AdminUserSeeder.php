<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@visitlog.test')],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => UserRole::Admin,
            ]
        );
    }
}
