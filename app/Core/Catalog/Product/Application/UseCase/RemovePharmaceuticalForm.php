<?php

namespace App\Core\Catalog\Product\Application\UseCase;

use App\Core\Catalog\Product\Application\Exception\PharmaceuticalFormInUse;
use App\Core\Catalog\Product\Application\Exception\PharmaceuticalFormNotFound;
use App\Core\Catalog\Product\Model\Repository\PharmaceuticalFormRepository;

final readonly class RemovePharmaceuticalForm
{
    public function __construct(
        private PharmaceuticalFormRepository $repository,
    ) {
    }

    public function execute(int $pharmaceuticalFormId): void
    {
        $entry = $this->repository->findById($pharmaceuticalFormId)
            ?? throw new PharmaceuticalFormNotFound((string)$pharmaceuticalFormId);

        if ($this->repository->isUsed($pharmaceuticalFormId)) {
            throw new PharmaceuticalFormInUse($entry->name);
        }

        $this->repository->removeById($pharmaceuticalFormId);
    }
}

