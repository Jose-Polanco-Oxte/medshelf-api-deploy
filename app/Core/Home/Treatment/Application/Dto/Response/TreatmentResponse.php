<?php

namespace App\Core\Home\Treatment\Application\Dto\Response;

use Carbon\Carbon;

readonly class TreatmentResponse
{
    public function __construct(
        public string  $id,
        public string  $profileId,
        public string  $productId,
        public string  $status,
        public float   $dose,
        public int     $frequencyHours,
        public Carbon  $startDate,
        public ?int    $days,
        public Carbon  $createdAt,
    )
    {
    }
}
