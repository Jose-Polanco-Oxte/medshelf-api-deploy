<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class UpdateTreatmentRequest
{
    public function __construct(
        public string  $treatmentId,
        public ?int    $frequencyValue,
        public ?string $frequencyUnit,
        public ?float  $doseQuantity,
        public ?string $endDate,
    )
    {
    }
}
