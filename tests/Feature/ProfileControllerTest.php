<?php

namespace Tests\Feature;

use App\Models\ProfileModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_profile_and_returns_201(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'name' => 'Maria',
            'relationship' => 'parent',
            'birthDate' => '1995-08-20',
            'allergies' => ['Pollen', 'Penicillin'],
        ], ['X-User-Id' => $user->public_id])
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Maria',
                'relationship' => 'parent',
                'birthDate' => '1995-08-20',
            ])
            ->assertJsonFragment(['allergies' => ['Pollen', 'Penicillin']]);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/profiles', [
            'relationship' => 'parent',
        ], ['X-User-Id' => $user->public_id])
            ->assertStatus(422);
    }

    public function test_index_returns_profiles_for_user(): void
    {
        $user = User::factory()->create();
        ProfileModel::factory()->create(['user_id' => $user->id, 'name' => 'Carlos']);

        $this->getJson('/api/profiles', ['X-User-Id' => $user->public_id])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Carlos']);
    }

    public function test_index_does_not_return_other_users_profiles(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        ProfileModel::factory()->create(['user_id' => $userA->id, 'name' => 'ProfileA']);
        ProfileModel::factory()->create(['user_id' => $userB->id, 'name' => 'ProfileB']);

        $response = $this->getJson('/api/profiles', ['X-User-Id' => $userA->public_id])
            ->assertStatus(200);

        $this->assertStringContainsString('ProfileA', $response->getContent());
        $this->assertStringNotContainsString('ProfileB', $response->getContent());
    }

    public function test_show_returns_profile_detail(): void
    {
        $user = User::factory()->create();
        $profile = ProfileModel::factory()->create(['user_id' => $user->id]);

        $this->getJson("/api/profiles/{$profile->public_id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $profile->public_id]);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $this->getJson('/api/profiles/non-existent-id')
            ->assertStatus(404);
    }
}
