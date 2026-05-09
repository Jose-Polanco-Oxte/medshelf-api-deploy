<?php

namespace Tests\Unit\Treatment;

use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Home\Treatment\Model\Treatment;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TreatmentTest extends TestCase
{
    private function makeTreatment(TreatmentStatus $status = TreatmentStatus::ACTIVE): Treatment
    {
        return Treatment::load(
            id: 'test-id',
            profileId: 'profile-id',
            itemId: 'item-id',
            status: $status,
            frequencyValue: 8,
            frequencyUnit: 'hours',
            doseQuantity: 1.0,
            startDate: Carbon::today(),
            endDate: null,
            createdAt: Carbon::now(),
        );
    }

    public function test_create_sets_status_to_active(): void
    {
        $treatment = Treatment::create(
            profileId: 'profile-id',
            itemId: 'item-id',
            frequencyValue: 8,
            frequencyUnit: 'hours',
            doseQuantity: 1.0,
            startDate: Carbon::today(),
            endDate: null,
        );

        $this->assertEquals(TreatmentStatus::ACTIVE, $treatment->getStatus());
    }

    public function test_pause_transitions_active_to_paused(): void
    {
        $treatment = $this->makeTreatment(TreatmentStatus::ACTIVE);
        $treatment->pause();

        $this->assertEquals(TreatmentStatus::PAUSED, $treatment->getStatus());
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

    public function test_update_only_modifies_non_null_fields(): void
    {
        $treatment = $this->makeTreatment();

        $treatment->update(
            frequencyValue: 24,
            frequencyUnit: null,
            doseQuantity: null,
            endDate: null,
        );

        $this->assertEquals(24, $treatment->getFrequencyValue());
        $this->assertEquals('hours', $treatment->getFrequencyUnit());
        $this->assertEquals(1.0, $treatment->getDoseQuantity());
    }

    public function test_update_sets_end_date_when_provided(): void
    {
        $treatment = $this->makeTreatment();

        $treatment->update(
            frequencyValue: null,
            frequencyUnit: null,
            doseQuantity: null,
            endDate: '2030-12-31',
        );

        $this->assertNotNull($treatment->getEndDate());
        $this->assertEquals('2030-12-31', $treatment->getEndDate()->toDateString());
    }
}
