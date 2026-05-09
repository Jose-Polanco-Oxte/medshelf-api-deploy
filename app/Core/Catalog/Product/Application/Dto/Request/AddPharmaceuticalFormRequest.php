<?php

namespace App\Core\Catalog\Product\Application\Dto\Request;

readonly class AddPharmaceuticalFormRequest
{
    public function __construct(
        public string $name,
        public string $consumptionType,
    ) {
    }
}

