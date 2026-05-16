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
        public string        $frequencyUnit,
        public Carbon        $startDate,
        public ?Carbon       $endDate,
        public Carbon        $createdAt,
    )
    {
    }
}
