<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class CreateTreatmentRequest
{
    public function __construct(
        public string  $profileId,
        public string  $itemId,
        public string  $houseId,
        public float   $dose,
        public string  $frequencyUnit,
        public string  $startDate,
        public ?string $endDate,
    )
    {
    }
}
