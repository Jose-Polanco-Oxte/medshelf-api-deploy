<?php

namespace App\Core\Home\House\Model\Repository;

use App\Core\Home\House\Model\House;

interface HouseRepository
{
    public function save(House $house): void;

    public function countPlaces(string $houseId): int;

    public function existsOtherPlaceWithSameNameInHouse(string $houseId, string $placeName, ?string $actual = null): bool;

    public function existsById(string $houseId);
}
