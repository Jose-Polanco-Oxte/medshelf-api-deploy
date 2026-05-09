<?php

namespace App\Core\Catalog\Product\Application\Exception;

use App\Core\Shared\Application\AppException;

class ActiveIngredientInUse extends AppException
{
    public function __construct(string $name)
    {
        parent::__construct("Active ingredient '$name' is in use and cannot be removed.");
    }
}

