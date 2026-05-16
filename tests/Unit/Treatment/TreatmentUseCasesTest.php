<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\UseCase\UpdateTreatment;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TreatmentUseCasesTest extends TestCase
{
    private TreatmentRepository $repository;

    public function test_modify_changes_status_to_paused_and_saves(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new UpdateTreatment($this->repository))->execute(
            $this->modifyRequest(['status' => 'paused'])
        );

        $this->assertEquals('paused', $response->status);
    }

    private function makeTreatment(TreatmentStatus $status = TreatmentStatus::ACTIVE): Treatment
    {
        return Treatment::load(
            id: 'treatment-uuid',
            profileId: 'profile-uuid',
            itemId: 'item-uuid',
            status: $status,
            dose: 1.0,
            frequencyUnit: 'hours',
            startDate: Carbon::today(),
            endDate: null,
            createdAt: Carbon::now(),
        );
    }

    private function modifyRequest(array $overrides = []): UpdateTreatmentRequest
    {
        return new UpdateTreatmentRequest(
            treatmentId: $overrides['treatmentId'] ?? 'treatment-uuid',
            dose: $overrides['dose'] ?? null,
            frequencyUnit: $overrides['frequencyUnit'] ?? null,
            status: $overrides['status'] ?? 'active',
            endDate: $overrides['endDate'] ?? null,
        );
    }

    // ── ModifyTreatment ───────────────────────────────────────────────────

    public function test_modify_changes_dose_and_saves(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new UpdateTreatment($this->repository))->execute(
            $this->modifyRequest(['dose' => 2, 'status' => 'paused'])
        );

        $this->assertEquals(2.0, $response->dose);
    }

    public function test_modify_changes_frequency_unit_and_saves(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new UpdateTreatment($this->repository))->execute(
            $this->modifyRequest(['frequencyUnit' => 'days', 'status' => 'paused'])
        );

        $this->assertEquals('days', $response->frequencyUnit);
    }

    public function test_modify_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        (new UpdateTreatment($this->repository))->execute(
            $this->modifyRequest(['treatmentId' => 'missing-uuid'])
        );
    }

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TreatmentRepository::class);
    }
}

