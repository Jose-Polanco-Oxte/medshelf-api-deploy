<?php

namespace Tests\Unit\Profile;

use App\Core\Home\Profile\Application\Dto\Request\AddProfileRequest;
use App\Core\Home\Profile\Application\UseCase\AddProfile;
use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use PHPUnit\Framework\TestCase;

class AddProfileTest extends TestCase
{
    private ProfileRepository $repository;
    private AddProfile $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProfileRepository::class);
        $this->useCase    = new AddProfile($this->repository);
    }

    public function test_execute_saves_profile_and_returns_response(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Profile::class));

        $request = new AddProfileRequest(
            userId: 'user-uuid',
            name: 'Maria',
            relationship: null,
            birthDate: '1990-01-01',
            allergies: [],
        );

        $response = $this->useCase->execute($request);

        $this->assertEquals('Maria', $response->name);
        $this->assertNull($response->relationship);
        $this->assertNotEmpty($response->id);
        $this->assertNotEmpty($response->createdAt);
    }

    public function test_execute_passes_relationship_to_profile(): void
    {
        $this->repository->expects($this->once())->method('save');

        $request = new AddProfileRequest(
            userId: 'user-uuid',
            name: 'Juan',
            relationship: 'parent',
            birthDate: '1990-01-01',
            allergies: [],
        );

        $response = $this->useCase->execute($request);

        $this->assertEquals('parent', $response->relationship);
    }

    public function test_execute_passes_user_id_to_saved_profile(): void
    {
        $capturedProfile = null;

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Profile $profile) use (&$capturedProfile) {
                $capturedProfile = $profile;
            });

        $request = new AddProfileRequest(
            userId: 'expected-user-uuid',
            name: 'Test',
            relationship: null,
            birthDate: '1990-01-01',
            allergies: [],
        );

        $this->useCase->execute($request);

        $this->assertNotNull($capturedProfile);
        $this->assertEquals('expected-user-uuid', $capturedProfile->getUserId());
    }

    public function test_execute_returns_response_with_same_name_as_request(): void
    {
        $this->repository->expects($this->once())->method('save');

        $request = new AddProfileRequest(
            userId: 'uid',
            name: 'Unique Name 12345',
            relationship: null,
            birthDate: '1990-01-01',
            allergies: [],
        );

        $response = $this->useCase->execute($request);

        $this->assertEquals('Unique Name 12345', $response->name);
    }
}
