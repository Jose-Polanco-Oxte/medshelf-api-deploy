<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Model\Profile;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Core\Home\Treatment\Application\Dto\Request\AddTreatmentRequest;
use App\Core\Home\Treatment\Application\UseCase\AddTreatment;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AddTreatmentTest extends TestCase
{
    private ProfileRepository $profileRepository;
    private TreatmentRepository $treatmentRepository;
    private AddTreatment $useCase;

    protected function setUp(): void
    {
        $this->profileRepository   = $this->createMock(ProfileRepository::class);
        $this->treatmentRepository = $this->createMock(TreatmentRepository::class);
        $this->useCase = new AddTreatment($this->profileRepository, $this->treatmentRepository);
    }

    private function makeProfile(): Profile
    {
        return Profile::load(
            id: 'profile-uuid',
            userId: 'user-uuid',
            name: 'Maria',
            relationship: null,
            createdAt: Carbon::now(),
        );
    }

    private function makeRequest(array $overrides = []): AddTreatmentRequest
    {
        return new AddTreatmentRequest(
            profileId: $overrides['profileId'] ?? 'profile-uuid',
            itemId: $overrides['itemId'] ?? 'item-uuid',
            frequencyValue: $overrides['frequencyValue'] ?? 8,
            frequencyUnit: $overrides['frequencyUnit'] ?? 'hours',
            doseQuantity: $overrides['doseQuantity'] ?? 1.0,
            startDate: $overrides['startDate'] ?? '2026-01-01',
            endDate: $overrides['endDate'] ?? null,
        );
    }

    public function test_execute_creates_active_treatment(): void
    {
        $profile = $this->makeProfile();

        $this->profileRepository
            ->method('findById')
            ->willReturn($profile);

        $this->treatmentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn(Treatment $t) => $t->getStatus() === TreatmentStatus::ACTIVE));

        $response = $this->useCase->execute($this->makeRequest());

        $this->assertEquals('active', $response->status);
    }

    public function test_execute_returns_response_with_correct_frequency(): void
    {
        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->treatmentRepository->expects($this->once())->method('save');

        $response = $this->useCase->execute($this->makeRequest([
            'frequencyValue' => 12,
            'frequencyUnit' => 'hours',
        ]));

        $this->assertEquals(12, $response->frequencyValue);
        $this->assertEquals('hours', $response->frequencyUnit);
    }

    public function test_execute_throws_profile_not_found_when_profile_missing(): void
    {
        $this->expectException(ProfileNotFound::class);

        $this->profileRepository->method('findById')->willReturn(null);
        $this->treatmentRepository->expects($this->never())->method('save');

        $this->useCase->execute($this->makeRequest());
    }

    public function test_execute_passes_correct_item_id_to_treatment(): void
    {
        $capturedTreatment = null;

        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->treatmentRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Treatment $t) use (&$capturedTreatment) {
                $capturedTreatment = $t;
            });

        $this->useCase->execute($this->makeRequest(['itemId' => 'specific-item-uuid']));

        $this->assertEquals('specific-item-uuid', $capturedTreatment->getItemId());
    }

    public function test_execute_accepts_optional_end_date(): void
    {
        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->treatmentRepository->expects($this->once())->method('save');

        $response = $this->useCase->execute($this->makeRequest(['endDate' => '2026-12-31']));

        $this->assertNotNull($response->endDate);
    }

    public function test_execute_without_end_date_leaves_it_null(): void
    {
        $this->profileRepository->method('findById')->willReturn($this->makeProfile());
        $this->treatmentRepository->expects($this->once())->method('save');

        $response = $this->useCase->execute($this->makeRequest(['endDate' => null]));

        $this->assertNull($response->endDate);
    }
}
