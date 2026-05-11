<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class AddTreatmentRequest
{
    public function __construct(
        public string  $profileId,
        public string  $itemId,
        public int     $frequencyValue,
        public string  $frequencyUnit,
        public float   $doseQuantity,
        public string  $startDate,
        public ?string $endDate,
    )
    {
    }
}
