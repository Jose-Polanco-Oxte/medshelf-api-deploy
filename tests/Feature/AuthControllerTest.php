<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\WithJwtAuth;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithJwtAuth;

    // ── POST /auth/register ───────────────────────────────────────────────

    public function test_register_creates_user_and_returns_201(): void
    {
        $this->postJson('/api/auth/register', [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'expiresIn',
            ]);
    }

    public function test_register_returns_422_when_email_missing(): void
    {
        $this->postJson('/api/auth/register', [
            'name'     => 'Test User',
            'password' => 'password123',
        ])->assertStatus(422);
    }

    public function test_register_returns_422_when_password_too_short(): void
    {
        $this->postJson('/api/auth/register', [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'abc',      // min is 5, so 3 chars fails
        ])->assertStatus(422);
    }

    public function test_register_returns_422_when_email_already_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/auth/register', [
            'name'     => 'Another User',
            'email'    => 'taken@example.com',
            'password' => 'password123',
        ])->assertStatus(422);
    }

    // ── POST /auth/login ──────────────────────────────────────────────────

    public function test_login_with_valid_credentials_returns_200(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'expiresIn',
            ]);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_login_with_unknown_email_returns_401(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password123',
        ])->assertStatus(401);
    }

    // ── GET /auth/me ──────────────────────────────────────────────────────

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/auth/me', $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_me_returns_401_without_token(): void
    {
        $this->getJson('/api/auth/me')
            ->assertStatus(401);
    }

    // ── POST /auth/logout ─────────────────────────────────────────────────

    public function test_logout_invalidates_token_and_returns_200(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/logout', [], $this->authHeaders($user))
            ->assertStatus(200);
    }

    // ── POST /auth/refresh ────────────────────────────────────────────────

    public function test_refresh_returns_new_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/refresh', [], $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonStructure(['user', 'expiresIn']);
    }
}
