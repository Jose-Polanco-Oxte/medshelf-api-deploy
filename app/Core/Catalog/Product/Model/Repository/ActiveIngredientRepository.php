<?php

namespace App\Core\Catalog\Product\Model\Repository;

use App\Core\Catalog\Product\Model\ActiveIngredientEntry;

interface ActiveIngredientRepository
{
    public function existsByName(string $name): bool;

    public function create(string $name): ActiveIngredientEntry;

    public function findById(int $id): ?ActiveIngredientEntry;

    public function removeById(int $id): void;

    public function isUsed(int $id): bool;
}

