<?php

namespace App\Core\Catalog\Product\Application\UseCase;

use App\Core\Catalog\Product\Application\Exception\ActiveIngredientInUse;
use App\Core\Catalog\Product\Application\Exception\ActiveIngredientNotFound;
use App\Core\Catalog\Product\Model\Repository\ActiveIngredientRepository;

final readonly class RemoveActiveIngredient
{
    public function __construct(
        private ActiveIngredientRepository $repository,
    ) {
    }

    public function execute(int $activeIngredientId): void
    {
        $entry = $this->repository->findById($activeIngredientId)
            ?? throw new ActiveIngredientNotFound((string)$activeIngredientId);

        if ($this->repository->isUsed($activeIngredientId)) {
            throw new ActiveIngredientInUse($entry->name);
        }

        $this->repository->removeById($activeIngredientId);
    }
}

