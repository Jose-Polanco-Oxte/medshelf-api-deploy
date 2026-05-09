<?php

namespace App\Core\Catalog\Product\Model;

use Carbon\Carbon;

readonly class ActiveIngredientEntry
{
    public function __construct(
        public int $id,
        public string $name,
        public Carbon $createdAt,
    ) {
    }
}

