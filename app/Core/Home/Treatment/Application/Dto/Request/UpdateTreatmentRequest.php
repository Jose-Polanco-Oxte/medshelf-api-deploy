<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class UpdateTreatmentRequest
{
    public function __construct(
        public string  $treatmentId,
        public ?int    $dose,
        public ?int    $frequencyHours,
        public ?string $status,
        public ?int    $days,
    )
    {
    }
}
