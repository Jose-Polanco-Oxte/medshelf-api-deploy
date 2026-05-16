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
        protected string          $productId,
        protected TreatmentStatus $status,
        protected float           $dose,
        protected int             $frequencyHours,
        protected Carbon          $startDate,
        protected ?int            $days,
        protected Carbon          $createdAt,
    )
    {
    }

    public static function create(
        string  $profileId,
        string  $productId,
        float   $dose,
        int     $frequencyHours,
        Carbon  $startDate,
        ?int    $days,
    ): Treatment
    {
        return new self(
            Utils::generateUUIDV4(),
            $profileId,
            $productId,
            TreatmentStatus::ACTIVE,
            $dose,
            $frequencyHours,
            $startDate,
            $days,
            Carbon::now(),
        );
    }

    public static function load(
        string          $id,
        string          $profileId,
        string          $productId,
        TreatmentStatus $status,
        float           $dose,
        int             $frequencyHours,
        Carbon          $startDate,
        ?int            $days,
        Carbon          $createdAt,
    ): Treatment
    {
        return new self(
            $id,
            $profileId,
            $productId,
            $status,
            $dose,
            $frequencyHours,
            $startDate,
            $days,
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

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getStatus(): TreatmentStatus
    {
        return $this->status;
    }

    public function getDose(): float
    {
        return $this->dose;
    }

    public function getFrequencyHours(): int
    {
        return $this->frequencyHours;
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getDays(): ?int
    {
        return $this->days;
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

    public function changeFrequencyHours(?int $frequencyHours): void
    {
        if ($frequencyHours !== null) {
            $this->frequencyHours = $frequencyHours;
        }
    }

    public function changeDays(?int $days): void
    {
        if ($days !== null) {
            $this->days = $days;
        }
    }
}
