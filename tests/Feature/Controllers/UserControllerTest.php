<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_users_index(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/index')
                ->has('users', 4)
            );
    }

    public function test_tracker_cannot_view_users_index(): void
    {
        $tracker = User::factory()->create();

        $this->actingAs($tracker)->get(route('users.index'))->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Jane Doe',
            'username' => 'janedoe',
            'password' => 'Password1!',
            'role' => 'tracker',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['username' => 'janedoe', 'name' => 'Jane Doe']);
    }

    public function test_tracker_cannot_create_user(): void
    {
        $tracker = User::factory()->create();

        $this->actingAs($tracker)->post(route('users.store'), [
            'name' => 'Jane Doe',
            'username' => 'janedoe',
            'password' => 'Password1!',
            'role' => 'tracker',
        ])->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'username' => 'janedoe',
            'password' => 'Password1!',
            'role' => 'tracker',
        ])->assertSessionHasErrors('name');
    }

    public function test_store_requires_unique_username(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['username' => 'taken']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Jane Doe',
            'username' => 'taken',
            'password' => 'Password1!',
            'role' => 'tracker',
        ])->assertSessionHasErrors('username');
    }

    public function test_store_requires_valid_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Jane Doe',
            'username' => 'janedoe',
            'password' => 'Password1!',
            'role' => 'superuser',
        ])->assertSessionHasErrors('role');
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => 'New Name',
            'username' => $user->username,
            'role' => 'tracker',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_admin_can_update_user_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => 'tracker',
            'password' => 'NewPassword1!',
        ]);

        $this->assertNotEquals($oldHash, $user->fresh()->password);
    }

    public function test_update_without_password_does_not_change_it(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->actingAs($admin)->patch(route('users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => 'tracker',
        ]);

        $this->assertEquals($oldHash, $user->fresh()->password);
    }

    public function test_tracker_cannot_update_user(): void
    {
        $tracker = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($tracker)->patch(route('users.update', $other), [
            'name' => 'Hacked',
            'username' => $other->username,
            'role' => 'tracker',
        ])->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertForbidden();
    }

    public function test_tracker_cannot_delete_user(): void
    {
        $tracker = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($tracker)->delete(route('users.destroy', $other))->assertForbidden();
    }
}
