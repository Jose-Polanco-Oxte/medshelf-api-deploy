<?php

namespace App\Providers\Core\Home\Treatment\Detail;

use App\Providers\Core\Home\Item\Resume\ItemResume;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;
use Carbon\Carbon;

readonly class TreatmentDetail
{
    public function __construct(
        public string        $id,
        public ProfileResume $profile,
        public ItemResume    $item,
        public string        $status,
        public float         $dose,
        public int           $frequencyHours,
        public Carbon        $startDate,
        public ?int          $days,
        public Carbon        $createdAt,
    )
    {
    }
}
