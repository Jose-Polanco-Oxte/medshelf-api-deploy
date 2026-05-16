<?php

namespace App\Core\Home\House\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class PlaceNotFound extends NotFoundException
{
    public function __construct(string $placeId)
    {
        parent::__construct('Place not found for id: ' . $placeId);
    }

    public static function somePlacesNotFound(): self
    {
        return new self('Some places not found for ids or not in house');
    }
}