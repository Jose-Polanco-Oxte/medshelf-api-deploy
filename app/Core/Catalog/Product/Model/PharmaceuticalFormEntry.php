<?php

namespace App\Core\Catalog\Product\Model;

use App\Core\Catalog\Product\Model\ValueObject\ConsumptionType;
use Carbon\Carbon;

readonly class PharmaceuticalFormEntry
{
    public function __construct(
        public int $id,
        public string $name,
        public ConsumptionType $consumptionType,
        public Carbon $createdAt,
    ) {
    }
}

