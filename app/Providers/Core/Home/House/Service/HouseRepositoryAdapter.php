<?php

namespace App\Providers\Core\Home\House\Service;

use App\Core\Home\House\Model\House;
use App\Core\Home\House\Model\Repository\HouseRepository;
use App\Models\HouseModel;
use App\Models\PlaceModel;
use App\Models\User;
use App\Providers\Core\InfrastructureException;

class HouseRepositoryAdapter implements HouseRepository
{
    public function save(House $house): void
    {
        $ownerId = User::where('public_id', $house->getOwnerId())->value('id')
            ?? throw new InfrastructureException(sprintf('Owner with id %s not found', $house->getOwnerId()));

        HouseModel::updateOrCreate(
            ['public_id' => $house->getId()],
            [
                'owner_id' => $ownerId,
                'name' => $house->getName()
            ]
        );
    }

    public function countPlaces(string $houseId): int
    {
        return PlaceModel::whereHas(
            'house', fn($q) => $q->where('public_id', $houseId)
        )->count();
    }

    public function existsOtherPlaceWithSameNameInHouse(string $houseId, string $placeName, ?string $actual = null): bool
    {
        $query = HouseModel::where('public_id', $houseId)
            ->whereHas('places', function ($q) use ($placeName, $actual) {
                $q->where('name', $placeName);
                if ($actual) {
                    $q->where('public_id', '!=', $actual);
                }
            });

        return $query->exists();
    }

    public function existsById(string $houseId): bool
    {
        return HouseModel::where('public_id', $houseId)->exists();
    }
}
