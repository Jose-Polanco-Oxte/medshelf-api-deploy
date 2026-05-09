<?php

namespace App\Providers\Core\Catalog\Product\Service;

use App\Core\Catalog\Product\Model\ActiveIngredientEntry;
use App\Core\Catalog\Product\Model\Repository\ActiveIngredientRepository;
use App\Models\ActiveIngredientModel;
use App\Models\ProductCompoundModel;

class ActiveIngredientRepositoryAdapter implements ActiveIngredientRepository
{
    public function existsByName(string $name): bool
    {
        return ActiveIngredientModel::where('name', $name)->exists();
    }

    public function create(string $name): ActiveIngredientEntry
    {
        $record = ActiveIngredientModel::create(['name' => $name]);

        return new ActiveIngredientEntry(
            id: $record->id,
            name: $record->name,
            createdAt: $record->created_at,
        );
    }

    public function findById(int $id): ?ActiveIngredientEntry
    {
        $record = ActiveIngredientModel::find($id);
        if (!$record) return null;

        return new ActiveIngredientEntry(
            id: $record->id,
            name: $record->name,
            createdAt: $record->created_at,
        );
    }

    public function removeById(int $id): void
    {
        ActiveIngredientModel::where('id', $id)->delete();
    }

    public function isUsed(int $id): bool
    {
        return ProductCompoundModel::where('active_ingredient_id', $id)->exists();
    }
}

