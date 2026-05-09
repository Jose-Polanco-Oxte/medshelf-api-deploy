<?php

namespace App\Core\Catalog\Product\Model\Repository;

use App\Core\Catalog\Product\Model\PharmaceuticalFormEntry;
use App\Core\Catalog\Product\Model\ValueObject\ConsumptionType;

interface PharmaceuticalFormRepository
{
    public function existsByName(string $name): bool;

    public function create(string $name, ConsumptionType $consumptionType): PharmaceuticalFormEntry;

    public function findById(int $id): ?PharmaceuticalFormEntry;

    public function removeById(int $id): void;

    public function isUsed(int $id): bool;
}

