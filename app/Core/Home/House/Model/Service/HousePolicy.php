<?php

namespace App\Core\Home\House\Model\Service;

use App\Core\Home\House\Model\Exception\HouseException;
use App\Core\Home\House\Model\Exception\PlaceException;
use App\Core\Home\House\Model\Repository\HouseRepository;

final readonly class HousePolicy
{
    public const int MAX_PLACES = 10;

    public function __construct(
        private HouseRepository $houseRepository,
    )
    {
    }

    public function assertCanAddPlace(string $houseId, string $placeName): void
    {
        $placesCount = $this->houseRepository->countPlaces($houseId);
        if ($placesCount >= self::MAX_PLACES) {
            throw PlaceException::cannotAddPlaceToHouseWithTooManyPlaces($houseId);
        }
        $this->houseRepository->existsOtherPlaceWithSameNameInHouse($houseId, $placeName) &&
        throw PlaceException::cannotAddPlaceWithSameNameInHouse($houseId);
    }

    public function assertExistsHouse(string $houseId): void
    {
        if (!$this->houseRepository->existsById($houseId)) {
            throw HouseException::houseNotFound();
        }
    }

    public function assertCanRemovePlace(string $houseId): void
    {
        $placesCount = $this->houseRepository->countPlaces($houseId);
        if ($placesCount <= 1) {
            throw PlaceException::cannotRemovePlaceFromHouseWithOnlyOnePlace($houseId);
        }
    }

    public function assertCanUpdatePlace(string $houseId, string $actualPlaceId, string $newPlaceName): void
    {
        $this->houseRepository->existsOtherPlaceWithSameNameInHouse($houseId, $newPlaceName, $actualPlaceId) &&
        throw PlaceException::cannotUpdatePlaceNameToSameNameInHouse($houseId);
    }

    public function assertCanRemovePlaces(string $houseId, int $placesToRemove): void
    {
        if ($placesToRemove > self::MAX_PLACES) {
            throw PlaceException::cannotRemoveMoreThanMaxPlacesFromHouse($houseId);
        }
        $placesCount = $this->houseRepository->countPlaces($houseId);
        if ($placesCount - $placesToRemove < 1) {
            throw PlaceException::cannotRemoveMorePlacesThanHouseHas($houseId);
        }
    }
}
