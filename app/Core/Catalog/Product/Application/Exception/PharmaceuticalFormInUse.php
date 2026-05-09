<?php

namespace App\Core\Catalog\Product\Application\Exception;

use App\Core\Shared\Application\AppException;

class PharmaceuticalFormInUse extends AppException
{
    public function __construct(string $name)
    {
        parent::__construct("Pharmaceutical form '$name' is in use and cannot be removed.");
    }
}

