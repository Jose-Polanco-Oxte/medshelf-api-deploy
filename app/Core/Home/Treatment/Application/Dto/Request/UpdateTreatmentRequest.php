<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class UpdateTreatmentRequest
{
    public function __construct(
        public string  $treatmentId,
        public ?int    $dose,
        public ?string $frequencyUnit,
        public ?string $status,
        public ?string $endDate,
    )
    {
    }
}
