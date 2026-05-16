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
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertSame('Maria', $profile->getName());
    }

    public function test_create_assigns_non_empty_uuid(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertNotEmpty($profile->getId());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $profile->getId()
        );
    }

    public function test_create_stores_user_id(): void
    {
        $profile = Profile::create(userId: 'user-uuid', name: 'Maria', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertEquals('user-uuid', $profile->getUserId());
    }

    public function test_create_with_relationship(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Juan', relationship: 'parent', birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertEquals('parent', $profile->getRelationship());
    }

    public function test_create_with_null_relationship(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Ana', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertNull($profile->getRelationship());
    }

    public function test_create_sets_created_at_to_now(): void
    {
        $before = Carbon::now()->subSecond();
        $profile = Profile::create(userId: 'user-id', name: 'Test', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);
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
            birthDate: Carbon::parse('1990-06-15'),
            allergies: [],
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
            birthDate: Carbon::parse('1990-01-01'),
            allergies: [],
            createdAt: Carbon::now(),
        );

        $this->assertNull($profile->getRelationship());
    }

    // ── two profiles created independently get different IDs ──────────────

    public function test_two_created_profiles_have_different_ids(): void
    {
        $a = Profile::create(userId: 'u1', name: 'A', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);
        $b = Profile::create(userId: 'u2', name: 'B', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $this->assertNotEquals($a->getId(), $b->getId());
    }

    // ── update ────────────────────────────────────────────────────────────

    public function test_update_changes_name_when_provided(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Old Name', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $profile->update('New Name', null, null);

        $this->assertEquals('New Name', $profile->getName());
    }

    public function test_update_changes_relationship_when_provided(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $profile->update(null, 'sibling', null);

        $this->assertEquals('sibling', $profile->getRelationship());
    }

    public function test_update_does_not_change_name_when_null(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Keep This', relationship: 'parent', birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $profile->update(null, 'child', null);

        $this->assertEquals('Keep This', $profile->getName());
    }

    public function test_update_does_not_change_relationship_when_null(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Maria', relationship: 'parent', birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $profile->update('Maria Updated', null, null);

        $this->assertEquals('parent', $profile->getRelationship());
    }

    public function test_update_can_change_both_fields(): void
    {
        $profile = Profile::create(userId: 'user-id', name: 'Old', relationship: 'sibling', birthDate: Carbon::parse('1990-01-01'), allergies: []);

        $profile->update('New', 'parent', null);

        $this->assertEquals('New', $profile->getName());
        $this->assertEquals('parent', $profile->getRelationship());
    }

    public function test_update_does_not_modify_id_or_user_id(): void
    {
        $profile = Profile::create(userId: 'original-user', name: 'Name', relationship: null, birthDate: Carbon::parse('1990-01-01'), allergies: []);
        $originalId = $profile->getId();
        $originalUserId = $profile->getUserId();

        $profile->update('Different Name', 'child', null);

        $this->assertEquals($originalId, $profile->getId());
        $this->assertEquals($originalUserId, $profile->getUserId());
    }
}
