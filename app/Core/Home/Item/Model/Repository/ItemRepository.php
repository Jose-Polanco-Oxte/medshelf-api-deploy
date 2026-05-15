<?php

namespace App\Core\Home\Item\Model\Repository;

use App\Core\Home\Item\Model\Item;

interface ItemRepository
{
    public function save(Item $item): void;

    public function findByIdAndHouseId(string $itemId, string $houseId): ?Item;

    public function findById(string $id): ?Item;

    public function remove(Item $item): void;
}
