<?php

namespace App\Core\Home\Storage\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class StorageNotFound extends NotFoundException
{
    public function __construct(string $placeId)
    {
        parent::__construct("Storage unit for place with id $placeId not found");
    }
}