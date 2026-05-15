<?php

namespace App\Providers\Core\Home\Item\Service;

use App\Core\Home\Item\Model\Item;
use App\Core\Home\Item\Model\Repository\ItemRepository;
use App\Models\ItemModel;
use App\Models\ProductModel;
use App\Models\StorageModel;
use App\Providers\Core\InfrastructureException;

class ItemRepositoryAdapter implements ItemRepository
{

    public function save(Item $item): void
    {
        $storageInternalId = StorageModel::where('public_id', $item->getStorageId())->value('id')
            ?? throw new InfrastructureException(sprintf('StorageId %s not found', $item->getStorageId()));
        $productInternalId = ProductModel::where('public_id', $item->getProductId())->value('id')
            ?? throw new InfrastructureException(sprintf('ProductId %s not found', $item->getProductId()));
        ItemModel::updateOrCreate(
            ['public_id' => $item->getId()],
            [
                'product_id' => $productInternalId,
                'storage_id' => $storageInternalId,
                'total_content' => $item->getTotalContent(),
                'expiration_date' => $item->getExpirationDate(),
            ]
        );
    }

    public function findByIdAndHouseId(string $itemId, string $houseId): ?Item
    {
        $record = ItemModel::with([
            'product' => fn($q) => $q->select('id', 'public_id'),
            'storage' => fn($q) => $q->select('id', 'public_id', 'place_id'),
        ])
            ->where('public_id', $itemId)
            ->whereHas('storage.place.house', fn($q) => $q->where('public_id', $houseId))
            ->first();
        if (!$record) {
            return null;
        }
        return $this->toDomain($record);
    }

    private function toDomain(ItemModel $itemModel): Item
    {
        return Item::load(
            id: $itemModel->public_id,
            productId: $itemModel->product->public_id,
            storageId: $itemModel->storage->public_id,
            totalContent: $itemModel->total_content,
            expirationDate: $itemModel->expiration_date,
            createdAt: $itemModel->created_at,
        );
    }

    public function remove(Item $item): void
    {
        ItemModel::where('public_id', $item->getId())->delete();
    }

    public function findById(string $id): ?Item
    {
        $record = ItemModel::with([
            'product' => fn($q) => $q->select('id', 'public_id'),
            'storage' => fn($q) => $q->select('id', 'public_id', 'place_id'),
        ])
            ->where('public_id', $id)
            ->first();
        if (!$record) {
            return null;
        }
        return $this->toDomain($record);
    }
}