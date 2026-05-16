<?php

namespace App\Providers\Core\Home\Treatment\View;

use App\Core\Shared\Domain\PaginableByCursor;

readonly class TreatmentView implements PaginableByCursor
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
    )
    {
    }

    public function getCursor(): string
    {
        return $this->id;
    }
}
