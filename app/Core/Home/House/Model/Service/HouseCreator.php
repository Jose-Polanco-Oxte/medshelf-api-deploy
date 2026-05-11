<?php

namespace App\Core\Home\House\Model\Service;

use App\Core\Home\House\Model\House;
use App\Core\Home\House\Model\Repository\HouseRepository;

final readonly class HouseCreator
{
    public function __construct(
        private PlaceCreator    $placeCreator,
        private HouseRepository $houseRepository,
    )
    {
    }

    public function create(string $ownerId, string $name): House
    {
        $house = House::create($ownerId, $name);
        $this->houseRepository->save($house);
        $this->placeCreator->create($house->getId(), 'Default Place');
        return $house;
    }
}