<?php

namespace App\Core\Catalog\Product\Application\Dto\Response;

use Carbon\Carbon;

readonly class ActiveIngredientResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public Carbon $createdAt,
    ) {
    }
}

