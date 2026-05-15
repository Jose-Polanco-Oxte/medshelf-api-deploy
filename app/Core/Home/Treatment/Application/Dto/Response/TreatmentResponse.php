<?php

namespace App\Core\Home\Treatment\Application\Dto\Response;

use Carbon\Carbon;

readonly class TreatmentResponse
{
    public function __construct(
        public string  $id,
        public string  $profileId,
        public string  $itemId,
        public string  $status,
        public float   $dose,
        public string  $frequencyUnit,
        public Carbon  $startDate,
        public ?Carbon $endDate,
        public Carbon  $createdAt,
    )
    {
    }
}
