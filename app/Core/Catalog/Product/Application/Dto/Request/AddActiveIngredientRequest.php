<?php

namespace App\Core\Catalog\Product\Application\Dto\Request;

readonly class AddActiveIngredientRequest
{
    public function __construct(
        public string $name,
    ) {
    }
}

