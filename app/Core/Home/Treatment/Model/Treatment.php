<?php

namespace App\Core\Home\Treatment\Model;

use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Shared\Domain\Utils;
use Carbon\Carbon;

final class Treatment
{
    private function __construct(
        protected string          $id,
        protected string          $profileId,
        protected string          $itemId,
        protected TreatmentStatus $status,
        protected int             $frequencyValue,
        protected string          $frequencyUnit,
        protected float           $doseQuantity,
        protected Carbon          $startDate,
        protected ?Carbon         $endDate,
        protected Carbon          $createdAt,
    )
    {
    }

    public static function create(
        string  $profileId,
        string  $itemId,
        int     $frequencyValue,
        string  $frequencyUnit,
        float   $doseQuantity,
        Carbon  $startDate,
        ?Carbon $endDate,
    ): Treatment
    {
        return new self(
            Utils::generateUUIDV4(),
            $profileId,
            $itemId,
            TreatmentStatus::ACTIVE,
            $frequencyValue,
            $frequencyUnit,
            $doseQuantity,
            $startDate,
            $endDate,
            Carbon::now(),
        );
    }

    public static function load(
        string          $id,
        string          $profileId,
        string          $itemId,
        TreatmentStatus $status,
        int             $frequencyValue,
        string          $frequencyUnit,
        float           $doseQuantity,
        Carbon          $startDate,
        ?Carbon         $endDate,
        Carbon          $createdAt,
    ): Treatment
    {
        return new self(
            $id,
            $profileId,
            $itemId,
            $status,
            $frequencyValue,
            $frequencyUnit,
            $doseQuantity,
            $startDate,
            $endDate,
            $createdAt,
        );
    }

    public function pause(): void
    {
        if ($this->status !== TreatmentStatus::ACTIVE) {
            throw TreatmentException::cannotPause($this->id);
        }
        $this->status = TreatmentStatus::PAUSED;
    }

    public function resume(): void
    {
        if ($this->status !== TreatmentStatus::PAUSED) {
            throw TreatmentException::cannotResume($this->id);
        }
        $this->status = TreatmentStatus::ACTIVE;
    }

    public function complete(): void
    {
        if ($this->status !== TreatmentStatus::ACTIVE) {
            throw TreatmentException::cannotComplete($this->id);
        }
        $this->status = TreatmentStatus::COMPLETED;
    }

    public function cancel(): void
    {
        if ($this->status !== TreatmentStatus::ACTIVE && $this->status !== TreatmentStatus::PAUSED) {
            throw TreatmentException::cannotCancel($this->id);
        }
        $this->status = TreatmentStatus::CANCELLED;
    }

    public function update(?int $frequencyValue, ?string $frequencyUnit, ?float $doseQuantity, ?string $endDate): void
    {
        if ($frequencyValue !== null) {
            $this->frequencyValue = $frequencyValue;
        }
        if ($frequencyUnit !== null) {
            $this->frequencyUnit = $frequencyUnit;
        }
        if ($doseQuantity !== null) {
            $this->doseQuantity = $doseQuantity;
        }
        if ($endDate !== null) {
            $this->endDate = Carbon::parse($endDate);
        }
    }

    public function assertCanRegisterDose(): void
    {
        if ($this->status !== TreatmentStatus::ACTIVE) {
            throw TreatmentException::cannotRegisterDose($this->id, $this->status->value);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getStatus(): TreatmentStatus
    {
        return $this->status;
    }

    public function getFrequencyValue(): int
    {
        return $this->frequencyValue;
    }

    public function getFrequencyUnit(): string
    {
        return $this->frequencyUnit;
    }

    public function getDoseQuantity(): float
    {
        return $this->doseQuantity;
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getEndDate(): ?Carbon
    {
        return $this->endDate;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
