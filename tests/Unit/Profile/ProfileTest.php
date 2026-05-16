<?php

namespace Tests\Unit\Profile;

use App\Core\Home\Profile\Model\Profile;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    // ── create ────────────────────────────────────────────────────────────

    public function test_create_sets_provided_name(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: null);

        $this->assertEquals('Maria', $profile->getName());
    }

    public function test_create_assigns_non_empty_uuid(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: null);

        $this->assertNotEmpty($profile->getId());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $profile->getId()
        );
    }

    public function test_create_stores_user_id(): void
    {
        $profile = Profile::create(userId: 'user-uuid', name: 'Maria', relationship: null);

        $this->assertEquals('user-uuid', $profile->getUserId());
    }

    public function test_create_with_relationship(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Juan', relationship: 'parent');

        $this->assertEquals('parent', $profile->getRelationship());
    }

    public function test_create_with_null_relationship(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Ana', relationship: null);

        $this->assertNull($profile->getRelationship());
    }

    public function test_create_sets_created_at_to_now(): void
    {
        $before = Carbon::now()->subSecond();
        $profile = Profile::create(userId: 'user-id', name: 'Test', relationship: null);
        $after = Carbon::now()->addSecond();

        $this->assertTrue($profile->getCreatedAt()->between($before, $after));
    }

    // ── load ──────────────────────────────────────────────────────────────

    public function test_load_restores_all_fields(): void
    {
        $createdAt = Carbon::parse('2026-01-01T10:00:00+00:00');

        $profile = Profile::load(
            id: 'fixed-id',
            userId: 'user-fixed',
            name: 'Carlos',
            relationship: 'sibling',
            createdAt: $createdAt,
        );

        $this->assertEquals('fixed-id', $profile->getId());
        $this->assertEquals('user-fixed', $profile->getUserId());
        $this->assertEquals('Carlos', $profile->getName());
        $this->assertEquals('sibling', $profile->getRelationship());
        $this->assertTrue($createdAt->equalTo($profile->getCreatedAt()));
    }

    public function test_load_allows_null_relationship(): void
    {
        $profile = Profile::load(
            id: 'some-id',
            userId: 'user-id',
            name: 'Luisa',
            relationship: null,
            createdAt: Carbon::now(),
        );

        $this->assertNull($profile->getRelationship());
    }

    // ── two profiles created independently get different IDs ──────────────

    public function test_two_created_profiles_have_different_ids(): void
    {
        $a = Profile::create(userId: 'u1', name: 'A', relationship: null);
        $b = Profile::create(userId: 'u2', name: 'B', relationship: null);

        $this->assertNotEquals($a->getId(), $b->getId());
    }
}
