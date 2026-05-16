<?php

namespace App\Core\Catalog\Product\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class ActiveIngredientNotFound extends NotFoundException
{
    public function __construct(string $name)
    {
        parent::__construct("Active ingredient '$name' does not exist.");
    }
}