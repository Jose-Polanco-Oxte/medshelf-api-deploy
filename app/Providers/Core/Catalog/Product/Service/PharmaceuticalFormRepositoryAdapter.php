<?php

namespace App\Providers\Core\Catalog\Product\Service;

use App\Core\Catalog\Product\Model\PharmaceuticalFormEntry;
use App\Core\Catalog\Product\Model\Repository\PharmaceuticalFormRepository;
use App\Core\Catalog\Product\Model\ValueObject\ConsumptionType;
use App\Models\PharmaceuticalFormModel;
use App\Models\ProductModel;

class PharmaceuticalFormRepositoryAdapter implements PharmaceuticalFormRepository
{
    public function existsByName(string $name): bool
    {
        return PharmaceuticalFormModel::where('name', $name)->exists();
    }

    public function create(string $name, ConsumptionType $consumptionType): PharmaceuticalFormEntry
    {
        $record = PharmaceuticalFormModel::create([
            'name' => $name,
            'consumption_type' => $consumptionType->value,
        ]);

        return new PharmaceuticalFormEntry(
            id: $record->id,
            name: $record->name,
            consumptionType: ConsumptionType::fromString($record->consumption_type),
            createdAt: $record->created_at,
        );
    }

    public function findById(int $id): ?PharmaceuticalFormEntry
    {
        $record = PharmaceuticalFormModel::find($id);
        if (!$record) return null;

        return new PharmaceuticalFormEntry(
            id: $record->id,
            name: $record->name,
            consumptionType: ConsumptionType::fromString($record->consumption_type),
            createdAt: $record->created_at,
        );
    }

    public function removeById(int $id): void
    {
        PharmaceuticalFormModel::where('id', $id)->delete();
    }

    public function isUsed(int $id): bool
    {
        return ProductModel::where('pharmaceutical_form_id', $id)->exists();
    }
}

