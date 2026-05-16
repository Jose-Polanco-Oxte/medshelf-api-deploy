<?php

namespace App\Providers\Core\Home\Treatment\View;

use App\Core\Shared\Domain\PaginableByCursor;
use App\Providers\Core\Home\Item\Resume\ProductResume;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;

readonly class TreatmentView implements PaginableByCursor
{
    public function __construct(
        public string        $id,
        public ProfileResume $profile,
        public ProductResume $product,
        public string        $status,
        public float         $dose,
        public int           $frequencyHours,
        public string        $startDate,
        public ?int          $days,
    )
    {
    }

    public function getCursor(): string
    {
        return $this->id;
    }
}
