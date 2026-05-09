<?php

namespace App\Providers\Core\Catalog\Product\View;

use App\Core\Shared\Domain\PaginableByCursor;

readonly class ActiveIngredientView implements PaginableByCursor
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public function getCursor(): string
    {
        return (string)$this->id;
    }
}

