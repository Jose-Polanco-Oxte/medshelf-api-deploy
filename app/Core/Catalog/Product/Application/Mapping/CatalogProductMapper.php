<?php

namespace App\Core\Catalog\Product\Application\Mapping;

use App\Core\Catalog\Product\Application\Dto\Response\ActiveIngredientResponse;
use App\Core\Catalog\Product\Application\Dto\Response\PharmaceuticalFormItemResponse;
use App\Core\Catalog\Product\Model\ActiveIngredientEntry;
use App\Core\Catalog\Product\Model\PharmaceuticalFormEntry;

final class CatalogProductMapper
{
    private function __construct()
    {
    }

    public static function toActiveIngredientResponse(ActiveIngredientEntry $entry): ActiveIngredientResponse
    {
        return new ActiveIngredientResponse(
            id: $entry->id,
            name: $entry->name,
            createdAt: $entry->createdAt,
        );
    }

    public static function toPharmaceuticalFormResponse(PharmaceuticalFormEntry $entry): PharmaceuticalFormItemResponse
    {
        return new PharmaceuticalFormItemResponse(
            id: $entry->id,
            name: $entry->name,
            consumptionType: $entry->consumptionType->value,
            createdAt: $entry->createdAt,
        );
    }
}

