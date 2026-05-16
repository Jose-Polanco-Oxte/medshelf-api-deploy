<?php

namespace App\Core\Home\House\Model\Service;

use App\Core\Home\House\Model\Place;
use App\Core\Home\House\Model\Repository\PlaceRepository;
use App\Core\Home\Storage\Model\Repository\StorageRepository;
use App\Core\Home\Storage\Model\Storage;
use App\Core\Shared\Application\TransactionManager;

final readonly class PlaceCreator
{
    public function __construct(
        private PlaceRepository    $placeRepository,
        private StorageRepository  $storageUnitRepository,
        private HousePolicy        $houseService,
        private TransactionManager $transactionManager,
    )
    {
    }

    public function addPlace(string $houseId, string $placeName): Place
    {
        // Business rule validation
        $this->houseService->assertCanAddPlace($houseId, $placeName);
        return $this->create($houseId, $placeName);
    }

    public function create(string $houseId, string $placeName): Place
    {
        $this->houseService->assertExistsHouse($houseId);
        return $this->transactionManager->run(
            function () use ($houseId, $placeName) {
                $place = Place::create($houseId, $placeName);
                $this->placeRepository->save($place);

                //Create initial storage
                $storageUnit = Storage::create($place->getId(), 'Default Storage');
                $this->storageUnitRepository->save($storageUnit);
                return $place;
            }
        );
    }
}