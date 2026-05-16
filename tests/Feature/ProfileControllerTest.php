<?php

namespace Tests\Feature;

use App\Models\ProfileModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Traits\WithJwtAuth;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithJwtAuth;

    // ── POST /api/profiles ────────────────────────────────────────────────

    public function test_store_creates_profile_and_returns_201(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'name'         => 'Maria',
            'relationship' => 'parent',
        ], $this->authHeaders($user))
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Maria', 'relationship' => 'parent']);
    }

    public function test_store_response_has_required_fields(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'name' => 'Carlos',
        ], $this->authHeaders($user))
            ->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'relationship', 'createdAt']);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'relationship' => 'parent',
        ], $this->authHeaders($user))
            ->assertStatus(422);
    }

    public function test_store_returns_401_without_auth(): void
    {
        $this->postJson('/api/profiles', ['name' => 'Maria'])
            ->assertStatus(401);
    }

    // ── GET /api/profiles ─────────────────────────────────────────────────

    public function test_index_returns_profiles_for_user(): void
    {
        $user = User::factory()->create();
        ProfileModel::factory()->create(['user_id' => $user->id, 'name' => 'Carlos']);

        $this->getJson('/api/profiles', $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Carlos']);
    }

    public function test_index_does_not_return_other_users_profiles(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        ProfileModel::factory()->create(['user_id' => $userA->id, 'name' => 'ProfileA']);
        ProfileModel::factory()->create(['user_id' => $userB->id, 'name' => 'ProfileB']);

        $response = $this->getJson('/api/profiles', $this->authHeaders($userA))
            ->assertStatus(200);

        $this->assertStringContainsString('ProfileA', $response->getContent());
        $this->assertStringNotContainsString('ProfileB', $response->getContent());
    }

    public function test_index_returns_401_without_auth(): void
    {
        $this->getJson('/api/profiles')->assertStatus(401);
    }

    // ── GET /api/profiles/{id} ────────────────────────────────────────────

    public function test_show_returns_profile_detail(): void
    {
        $user    = User::factory()->create();
        $profile = ProfileModel::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/profiles/{$profile->public_id}", $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $profile->public_id]);
    }

    public function test_show_response_has_required_fields(): void
    {
        $user    = User::factory()->create();
        $profile = ProfileModel::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/profiles/{$profile->public_id}", $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'relationship', 'createdAt']);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/profiles/non-existent-id', $this->authHeaders($user))
            ->assertStatus(404);
    }
}
