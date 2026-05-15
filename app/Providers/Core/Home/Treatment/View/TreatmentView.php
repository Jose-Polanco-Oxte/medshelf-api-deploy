<?php

namespace App\Providers\Core\Home\Treatment\View;

use App\Core\Shared\Domain\PaginableByCursor;
use App\Providers\Core\Home\Item\Resume\ItemResume;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;

readonly class TreatmentView implements PaginableByCursor
{
    public function __construct(
        public string        $id,
        public ProfileResume $profile,
        public ItemResume    $item,
        public string        $status,
        public float         $dose,
        public string        $frequencyUnit,
        public string        $startDate,
        public ?string       $endDate,
    )
    {
    }

    public function getCursor(): string
    {
        return $this->id;
    }
}
