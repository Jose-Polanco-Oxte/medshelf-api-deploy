<?php

namespace App\Core\Catalog\Product\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class ProductNotFound extends NotFoundException
{
    public function __construct(string $productId)
    {
        parent::__construct('Product not found for id: ' . $productId);
    }
}