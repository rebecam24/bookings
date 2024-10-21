<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_new_user_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token',
                     'message'
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function it_fails_registration_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // Nombre vacío
            'email' => 'not-an-email', // Email no válido
            'password' => '123', // Contraseña demasiado corta
        ]);

        $response->assertStatus(400)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_allows_user_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'expires_in',
                     'user' => ['name'],
                     'role',
                     'message',
                 ]);
    }

    /** @test */
    public function it_denies_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Invalid credentials']);
    }

    /** @test */
    public function it_logs_out_user_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout')
          ->assertStatus(200)
          ->assertJson(['message' => 'Session closed successfully.']);
    }

    /** @test */
    public function it_fails_logout_when_user_is_not_authenticated()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Token not provided']);
    }
}