<?php

namespace App\Core\Catalog\Product\Application\UseCase;

use App\Core\Catalog\Product\Application\Dto\Request\AddActiveIngredientRequest;
use App\Core\Catalog\Product\Application\Dto\Response\ActiveIngredientResponse;
use App\Core\Catalog\Product\Application\Exception\ActiveIngredientAlreadyExists;
use App\Core\Catalog\Product\Application\Mapping\CatalogProductMapper;
use App\Core\Catalog\Product\Model\Repository\ActiveIngredientRepository;

final readonly class AddActiveIngredient
{
    public function __construct(
        private ActiveIngredientRepository $repository,
    ) {
    }

    public function execute(AddActiveIngredientRequest $request): ActiveIngredientResponse
    {
        if ($this->repository->existsByName($request->name)) {
            throw new ActiveIngredientAlreadyExists($request->name);
        }

        $entry = $this->repository->create($request->name);
        return CatalogProductMapper::toActiveIngredientResponse($entry);
    }
}

