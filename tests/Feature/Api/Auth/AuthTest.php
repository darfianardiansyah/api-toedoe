<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'user']);
    }
    public function test_user_cannot_login_with_incorrect_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_register_and_receive_token(): void
    {
        $payload = [
            'name' => 'new user',
            'email' => $email = 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertCreated();
        $response->assertJsonStructure(['token', 'user']);
        $this->assertDatabaseHas('users', [
            'email' => $email
        ]);
    }
    public function test_user_can_register_with_invalid_data(): void
    {
        $payload = [
            'name' => '',
            'email' => $email = 'newuser',
            'password' => 'pas',
            'password_confirmation' => 'pass',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('laravel_api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertNoContent();

        $this->app['auth']->forgetGuards();

        $protected = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $protected->assertStatus(401);
    }
}
