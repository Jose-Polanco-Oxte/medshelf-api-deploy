<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TreatmentTest extends TestCase
{
    public function test_create_sets_status_to_active(): void
    {
        $treatment = Treatment::create(
            profileId: 'profile-id',
            productId: 'product-id',
            dose: 8,
            frequencyHours: 8,
            startDate: Carbon::today(),
            days: null,
        );

        $this->assertEquals(TreatmentStatus::ACTIVE, $treatment->getStatus());
    }

    public function test_pause_transitions_active_to_paused(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);
        $treatment->pause();

        $this->assertEquals(TreatmentStatus::PAUSED, $treatment->getStatus());
    }

    private function makeTreatment(TreatmentStatus $status = TreatmentStatus::ACTIVE): Treatment
    {
        return Treatment::load(
            id: 'test-id',
            profileId: 'profile-id',
            productId: 'product-id',
            status: $status,
            dose: 8,
            frequencyHours: 8,
            startDate: Carbon::today(),
            days: null,
            createdAt: Carbon::now(),
        );
    }

    public function test_pause_throws_when_not_active(): void
    {
        $this->expectException(TreatmentException::class);

        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);
        $treatment->pause();
    }

    public function test_resume_transitions_paused_to_active(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);
        $treatment->resume();

        $this->assertEquals(TreatmentStatus::ACTIVE, $treatment->getStatus());
    }

    public function test_resume_throws_when_not_paused(): void
    {
        $this->expectException(TreatmentException::class);

        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);
        $treatment->resume();
    }

    public function test_complete_transitions_active_to_completed(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);
        $treatment->complete();

        $this->assertEquals(TreatmentStatus::COMPLETED, $treatment->getStatus());
    }

    public function test_complete_throws_when_not_active(): void
    {
        $this->expectException(TreatmentException::class);

        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);
        $treatment->complete();
    }

    public function test_cancel_from_active(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);
        $treatment->cancel();

        $this->assertEquals(TreatmentStatus::CANCELLED, $treatment->getStatus());
    }

    public function test_cancel_from_paused(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);
        $treatment->cancel();

        $this->assertEquals(TreatmentStatus::CANCELLED, $treatment->getStatus());
    }

    public function test_cancel_throws_when_completed(): void
    {
        $this->expectException(TreatmentException::class);

        $treatment = $this->makeTreatment(TreatmentStatus::COMPLETED);
        $treatment->cancel();
    }

    public function test_assert_can_register_dose_passes_when_active(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);

        // Should not throw
        $treatment->assertCanRegisterDose();
        $this->addToAssertionCount(1);
    }

    public function test_assert_can_register_dose_throws_when_paused(): void
    {
        $this->expectException(TreatmentException::class);

        $treatment = $this->makeTreatment(TreatmentStatus::PAUSED);
        $treatment->assertCanRegisterDose();
    }
}
