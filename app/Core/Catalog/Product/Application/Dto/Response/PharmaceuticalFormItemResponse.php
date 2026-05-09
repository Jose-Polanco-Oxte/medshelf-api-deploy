<?php

namespace App\Core\Catalog\Product\Application\Dto\Response;

use Carbon\Carbon;

readonly class PharmaceuticalFormItemResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $consumptionType,
        public Carbon $createdAt,
    ) {
    }
}

