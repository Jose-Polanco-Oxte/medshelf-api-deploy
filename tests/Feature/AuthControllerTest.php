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

    // ── DELETE /auth/account ──────────────────────────────────────────────

    public function test_delete_account_soft_deletes_user_and_returns_200(): void
    {
        $user = User::factory()->create();

        $this->deleteJson('/api/auth/account', [], $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Account deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_delete_account_returns_401_without_auth(): void
    {
        $this->deleteJson('/api/auth/account')
            ->assertStatus(401);
    }

    // ── X-Auth-Type: JWT (dual auth) ──────────────────────────────────────

    public function test_jwt_auth_type_header_grants_access(): void
    {
        $user = User::factory()->create();

        // Use X-Auth-Type: JWT so the middleware passes the Authorization header through directly
        $this->getJson('/api/auth/me', $this->authHeaders($user, ['X-Auth-Type' => 'JWT']))
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_cookie_auth_type_header_grants_access(): void
    {
        $user  = User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        // Pass token via cookie with X-Auth-Type: Cookie (default behaviour).
        // access_token is excluded from EncryptCookies, so we pass the raw JWT.
        $this->call('GET', '/api/auth/me', [], ['access_token' => $token], [], [
            'HTTP_X-Auth-Type'  => 'Cookie',
            'HTTP_ACCEPT'       => 'application/json',
        ])->assertStatus(200)
          ->assertJsonFragment(['email' => $user->email]);
    }
}
