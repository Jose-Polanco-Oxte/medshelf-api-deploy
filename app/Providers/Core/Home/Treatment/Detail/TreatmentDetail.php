<?php

namespace App\Providers\Core\Home\Treatment\Detail;

use App\Providers\Core\Home\Item\Resume\ProductResume;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;
use Carbon\Carbon;

readonly class TreatmentDetail
{
    public function __construct(
        public string        $id,
        public ProfileResume $profile,
        public ProductResume $product,
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
