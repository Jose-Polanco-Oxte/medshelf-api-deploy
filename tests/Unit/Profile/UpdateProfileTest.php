<?php

namespace Tests\Unit\Profile;

use App\Core\Home\Profile\Application\Dto\Request\UpdateProfileRequest;
use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Application\UseCase\UpdateProfile;
use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class UpdateProfileTest extends TestCase
{
    private ProfileRepository $repository;
    private UpdateProfile $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProfileRepository::class);
        $this->useCase    = new UpdateProfile($this->repository);
    }

    private function makeProfile(string $name = 'Original', ?string $relationship = null): Profile
    {
        return Profile::load(
            id: 'profile-uuid',
            userId: 'user-uuid',
            name: $name,
            relationship: $relationship,
            createdAt: Carbon::parse('2026-01-01T00:00:00+00:00'),
        );
    }

    public function test_execute_throws_not_found_when_profile_missing(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(ProfileNotFound::class);

        $this->useCase->execute(new UpdateProfileRequest(
            profileId: 'missing-uuid',
            name: 'New Name',
            relationship: null,
        ));
    }

    public function test_execute_updates_name_and_saves(): void
    {
        $profile = $this->makeProfile('Original');

        $this->repository->method('findById')->willReturn($profile);
        $this->repository->expects($this->once())->method('save')->with($profile);

        $response = $this->useCase->execute(new UpdateProfileRequest(
            profileId: 'profile-uuid',
            name: 'Updated',
            relationship: null,
        ));

        $this->assertEquals('Updated', $response->name);
    }

    public function test_execute_updates_relationship_and_saves(): void
    {
        $profile = $this->makeProfile('Maria', null);

        $this->repository->method('findById')->willReturn($profile);
        $this->repository->expects($this->once())->method('save');

        $response = $this->useCase->execute(new UpdateProfileRequest(
            profileId: 'profile-uuid',
            name: null,
            relationship: 'parent',
        ));

        $this->assertEquals('parent', $response->relationship);
        $this->assertEquals('Maria', $response->name); // unchanged
    }

    public function test_execute_returns_response_with_correct_id(): void
    {
        $profile = $this->makeProfile();

        $this->repository->method('findById')->willReturn($profile);
        $this->repository->method('save');

        $response = $this->useCase->execute(new UpdateProfileRequest(
            profileId: 'profile-uuid',
            name: 'Any',
            relationship: null,
        ));

        $this->assertEquals('profile-uuid', $response->id);
    }

    public function test_execute_does_not_save_when_profile_not_found(): void
    {
        $this->repository->method('findById')->willReturn(null);
        $this->repository->expects($this->never())->method('save');

        try {
            $this->useCase->execute(new UpdateProfileRequest(
                profileId: 'none',
                name: 'X',
                relationship: null,
            ));
        } catch (ProfileNotFound) {
            // expected
        }
    }
}
