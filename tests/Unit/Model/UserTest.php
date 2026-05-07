<?php

namespace Tests\Unit\Model;

use App\Enums\UserRole;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_admin_role_returns_true_for_is_admin(): void
    {
        $user = new User;
        $user->role = UserRole::Admin;

        $this->assertTrue($user->isAdmin());
    }

    public function test_tracker_role_returns_false_for_is_admin(): void
    {
        $user = new User;
        $user->role = UserRole::Tracker;

        $this->assertFalse($user->isAdmin());
    }
}
