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
            'birthDate'    => '1995-08-20',
            'allergies'    => ['Pollen', 'Penicillin'],
        ], $this->authHeaders($user))
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Maria',
                'relationship' => 'parent',
                'birthDate' => '1995-08-20',
            ])
            ->assertJsonFragment(['allergies' => ['Pollen', 'Penicillin']]);
    }

    public function test_store_response_has_required_fields(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'name' => 'Carlos',
            'birthDate' => '1990-01-01',
        ], $this->authHeaders($user))
            ->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'relationship', 'birthDate', 'allergies', 'createdAt']);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'relationship' => 'parent',
            'birthDate'    => '1990-01-01',
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

    // ── PATCH /api/profiles/{id} ──────────────────────────────────────────

    public function test_update_changes_name_and_returns_200(): void
    {
        $user    = User::factory()->create();
        $profile = ProfileModel::factory()->create(['user_id' => $user->id, 'name' => 'Old']);

        $this->patchJson("/api/profiles/{$profile->public_id}", [
            'name' => 'New Name',
        ], $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_update_changes_relationship_only(): void
    {
        $user    = User::factory()->create();
        $profile = ProfileModel::factory()->create([
            'user_id'      => $user->id,
            'name'         => 'Maria',
            'relationship' => null,
        ]);

        $this->patchJson("/api/profiles/{$profile->public_id}", [
            'relationship' => 'sibling',
        ], $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Maria', 'relationship' => 'sibling']);
    }

    public function test_update_returns_404_when_profile_not_found(): void
    {
        $user = User::factory()->create();

        $this->patchJson('/api/profiles/non-existent-uuid', [
            'name' => 'Whatever',
        ], $this->authHeaders($user))
            ->assertStatus(404);
    }

    public function test_update_returns_401_without_auth(): void
    {
        $this->patchJson('/api/profiles/some-uuid', ['name' => 'X'])
            ->assertStatus(401);
    }

    public function test_update_response_has_required_fields(): void
    {
        $user    = User::factory()->create();
        $profile = ProfileModel::factory()->create(['user_id' => $user->id]);

        $this->patchJson("/api/profiles/{$profile->public_id}", [
            'name' => 'Updated',
        ], $this->authHeaders($user))
            ->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'relationship', 'createdAt']);
    }
}
