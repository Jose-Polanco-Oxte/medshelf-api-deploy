<?php

namespace App\Core\Home\Profile\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class ProfileNotFound extends NotFoundException
{
    public function __construct(string $profileId)
    {
        parent::__construct('Profile not found for id: ' . $profileId);
    }
}
