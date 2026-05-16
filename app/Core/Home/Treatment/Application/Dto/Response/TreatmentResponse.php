<?php

namespace App\Core\Home\Treatment\Application\Dto\Response;

readonly class TreatmentResponse
{
    public function __construct(
        public string  $id,
        public array   $profile,
        public array   $item,
        public string  $status,
        public int     $frequencyValue,
        public string  $frequencyUnit,
        public float   $doseQuantity,
        public string  $startDate,
        public ?string $endDate,
        public string  $createdAt,
    )
    {
    }
}
