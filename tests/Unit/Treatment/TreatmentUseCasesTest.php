<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Treatment\Application\Dto\Request\TreatmentActionRequest;
use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\UseCase\CancelTreatment;
use App\Core\Home\Treatment\Application\UseCase\CompleteTreatment;
use App\Core\Home\Treatment\Application\UseCase\PauseTreatment;
use App\Core\Home\Treatment\Application\UseCase\ResumeTreatment;
use App\Core\Home\Treatment\Application\UseCase\UpdateTreatment;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TreatmentUseCasesTest extends TestCase
{
    private TreatmentRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TreatmentRepository::class);
    }

    private function makeTreatment(TreatmentStatus $status = TreatmentStatus::ACTIVE): Treatment
    {
        return Treatment::load(
            id: 'treatment-uuid',
            profileId: 'profile-uuid',
            itemId: 'item-uuid',
            status: $status,
            frequencyValue: 8,
            frequencyUnit: 'hours',
            doseQuantity: 1.0,
            startDate: Carbon::today(),
            endDate: null,
            createdAt: Carbon::now(),
        );
    }

    private function actionRequest(): TreatmentActionRequest
    {
        return new TreatmentActionRequest(treatmentId: 'treatment-uuid');
    }

    // ── PauseTreatment ────────────────────────────────────────────────────

    public function test_pause_saves_treatment_and_returns_paused_status(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new PauseTreatment($this->repository))->execute($this->actionRequest());

        $this->assertEquals('paused', $response->status);
    }

    public function test_pause_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        (new PauseTreatment($this->repository))->execute($this->actionRequest());
    }

    // ── ResumeTreatment ───────────────────────────────────────────────────

    public function test_resume_saves_treatment_and_returns_active_status(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new ResumeTreatment($this->repository))->execute($this->actionRequest());

        $this->assertEquals('active', $response->status);
    }

    public function test_resume_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        (new ResumeTreatment($this->repository))->execute($this->actionRequest());
    }

    // ── CompleteTreatment ─────────────────────────────────────────────────

    public function test_complete_saves_and_returns_completed_status(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new CompleteTreatment($this->repository))->execute($this->actionRequest());

        $this->assertEquals('completed', $response->status);
    }

    public function test_complete_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        (new CompleteTreatment($this->repository))->execute($this->actionRequest());
    }

    // ── CancelTreatment ───────────────────────────────────────────────────

    public function test_cancel_active_saves_and_returns_cancelled_status(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new CancelTreatment($this->repository))->execute($this->actionRequest());

        $this->assertEquals('cancelled', $response->status);
    }

    public function test_cancel_paused_also_succeeds(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $response = (new CancelTreatment($this->repository))->execute($this->actionRequest());

        $this->assertEquals('cancelled', $response->status);
    }

    public function test_cancel_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        (new CancelTreatment($this->repository))->execute($this->actionRequest());
    }

    // ── UpdateTreatment ───────────────────────────────────────────────────

    public function test_update_modifies_frequency_and_saves(): void
    {
        $treatment = $this->makeTreatment();

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $request = new UpdateTreatmentRequest(
            treatmentId: 'treatment-uuid',
            frequencyValue: 24,
            frequencyUnit: 'hours',
            doseQuantity: null,
            endDate: null,
        );

        $response = (new UpdateTreatment($this->repository))->execute($request);

        $this->assertEquals(24, $response->frequencyValue);
        $this->assertEquals('hours', $response->frequencyUnit);
    }

    public function test_update_preserves_unspecified_fields(): void
    {
        $treatment = $this->makeTreatment();

        $this->repository->method('findById')->willReturn($treatment);
        $this->repository->expects($this->once())->method('save');

        $request = new UpdateTreatmentRequest(
            treatmentId: 'treatment-uuid',
            frequencyValue: null,
            frequencyUnit: null,
            doseQuantity: null,
            endDate: null,
        );

        $response = (new UpdateTreatment($this->repository))->execute($request);

        $this->assertEquals(8, $response->frequencyValue);
        $this->assertEquals('hours', $response->frequencyUnit);
        $this->assertEquals(1.0, $response->doseQuantity);
    }

    public function test_update_throws_when_treatment_not_found(): void
    {
        $this->expectException(TreatmentNotFound::class);

        $this->repository->method('findById')->willReturn(null);

        $request = new UpdateTreatmentRequest(
            treatmentId: 'missing-uuid',
            frequencyValue: 12,
            frequencyUnit: null,
            doseQuantity: null,
            endDate: null,
        );

        (new UpdateTreatment($this->repository))->execute($request);
    }
}
