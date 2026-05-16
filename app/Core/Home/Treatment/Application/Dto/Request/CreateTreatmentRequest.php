<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class CreateTreatmentRequest
{
    public function __construct(
        public string  $profileId,
        public string  $productId,
        public float   $dose,
        public int     $frequencyHours,
        public string  $startDate,
        public ?int    $days,
    )
    {
    }
}
