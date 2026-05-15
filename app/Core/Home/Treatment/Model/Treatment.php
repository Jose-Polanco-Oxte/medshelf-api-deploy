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
        protected float           $dose,
        protected string          $frequencyUnit,
        protected Carbon          $startDate,
        protected ?Carbon         $endDate,
        protected Carbon          $createdAt,
    )
    {
    }

    public static function create(
        string  $profileId,
        string  $itemId,
        float   $dose,
        string  $frequencyUnit,
        Carbon  $startDate,
        ?Carbon $endDate,
    ): Treatment
    {
        return new self(
            Utils::generateUUIDV4(),
            $profileId,
            $itemId,
            TreatmentStatus::ACTIVE,
            $dose,
            $frequencyUnit,
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
        float           $dose,
        string          $frequencyUnit,
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
            $dose,
            $frequencyUnit,
            $startDate,
            $endDate,
            $createdAt,
        );
    }

    public function changeStatus(TreatmentStatus $status): void
    {
        switch ($status) {
            case TreatmentStatus::ACTIVE:
                $this->resume();
                break;
            case TreatmentStatus::PAUSED:
                $this->pause();
                break;
            case TreatmentStatus::COMPLETED:
                $this->complete();
                break;
            case TreatmentStatus::CANCELLED:
                $this->cancel();
                break;
        }
    }

    public function resume(): void
    {
        if ($this->status !== TreatmentStatus::PAUSED) {
            throw TreatmentException::cannotResume($this->id);
        }
        $this->status = TreatmentStatus::ACTIVE;
    }

    public function pause(): void
    {
        if ($this->status !== TreatmentStatus::ACTIVE) {
            throw TreatmentException::cannotPause($this->id);
        }
        $this->status = TreatmentStatus::PAUSED;
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

    public function getDose(): float
    {
        return $this->dose;
    }

    public function getFrequencyUnit(): string
    {
        return $this->frequencyUnit;
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

    public function changeDose(?float $dose): void
    {
        if ($dose !== null) {
            $this->dose = $dose;
        }
    }

    public function changeFrequencyUnit(?string $frequencyUnit): void
    {
        if ($frequencyUnit !== null) {
            $this->frequencyUnit = $frequencyUnit;
        }
    }

    public function changeEndDate(?Carbon $endDate): void
    {
        if ($endDate !== null) {
            $this->endDate = $endDate;
        }
    }
}
