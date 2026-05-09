<?php

namespace App\Providers\Core\Catalog\Product\View;

use App\Core\Shared\Domain\PaginableByCursor;

readonly class PharmaceuticalFormView implements PaginableByCursor
{
    public function __construct(
        public int $id,
        public string $name,
        public string $consumptionType,
    ) {
    }

    public function getCursor(): string
    {
        return (string)$this->id;
    }
}

