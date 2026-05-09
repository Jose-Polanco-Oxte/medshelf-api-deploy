<?php

namespace App\Core\Catalog\Product\Application\UseCase;

use App\Core\Catalog\Product\Application\Dto\Request\AddPharmaceuticalFormRequest;
use App\Core\Catalog\Product\Application\Dto\Response\PharmaceuticalFormItemResponse;
use App\Core\Catalog\Product\Application\Exception\PharmaceuticalFormAlreadyExists;
use App\Core\Catalog\Product\Application\Mapping\CatalogProductMapper;
use App\Core\Catalog\Product\Model\Repository\PharmaceuticalFormRepository;
use App\Core\Catalog\Product\Model\ValueObject\ConsumptionType;

final readonly class AddPharmaceuticalForm
{
    public function __construct(
        private PharmaceuticalFormRepository $repository,
    ) {
    }

    public function execute(AddPharmaceuticalFormRequest $request): PharmaceuticalFormItemResponse
    {
        if ($this->repository->existsByName($request->name)) {
            throw new PharmaceuticalFormAlreadyExists($request->name);
        }

        $consumptionType = ConsumptionType::fromString($request->consumptionType);
        $entry = $this->repository->create($request->name, $consumptionType);
        return CatalogProductMapper::toPharmaceuticalFormResponse($entry);
    }
}

