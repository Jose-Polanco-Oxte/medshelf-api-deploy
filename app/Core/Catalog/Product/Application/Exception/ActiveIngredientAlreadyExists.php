<?php

namespace App\Core\Catalog\Product\Application\Exception;

use App\Core\Shared\Application\AppException;

class ActiveIngredientAlreadyExists extends AppException
{
    public function __construct(string $name)
    {
        parent::__construct("Active ingredient '$name' already exists.");
    }
}

