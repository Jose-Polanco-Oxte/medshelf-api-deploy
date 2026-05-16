<?php

namespace App\Providers\Core\Home\Item\View;

use App\Core\Shared\Domain\PaginableByCursor;
use App\Providers\Core\Home\Item\Resume\ItemResume;
use App\Providers\Core\Home\Item\Resume\PlaceResume;

readonly class ConsumptionView implements PaginableByCursor
{
    public function __construct(
        public string      $id,
        public ItemResume  $item,
        public PlaceResume $place,
        public float       $amount,
        public string      $consumedAt
    )
    {
    }

    public function getCursor(): string
    {
        return $this->id;
    }
}