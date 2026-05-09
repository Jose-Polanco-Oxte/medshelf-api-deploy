<?php

namespace App\Providers\Core\Home\Treatment\Detail;

use App\Providers\Core\Home\Treatment\Resume\ItemResume;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;
use Carbon\Carbon;

readonly class TreatmentDetail
{
    public function __construct(
        public string        $id,
        public ProfileResume $profile,
        public ItemResume    $item,
        public string        $status,
        public int           $frequencyValue,
        public string        $frequencyUnit,
        public float         $doseQuantity,
        public Carbon        $startDate,
        public ?Carbon       $endDate,
        public Carbon        $createdAt,
    )
    {
    }
}
