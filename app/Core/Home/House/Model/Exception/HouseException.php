<?php

namespace App\Core\Home\House\Model\Exception;

use App\Core\Shared\Domain\DomainException;

class HouseException extends DomainException
{
    public static function houseNotFound(): self
    {
        return new self('House not found.');
    }

}