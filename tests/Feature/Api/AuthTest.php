<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_token_with_valid_credentials(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/tokens/create', [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_user_cannot_create_token_with_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/tokens/create', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_api(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $user->email]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
