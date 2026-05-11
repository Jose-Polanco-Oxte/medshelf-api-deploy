<?php

namespace App\Core\Home\Profile\Application\Exception;

use App\Core\Shared\Application\AppException;

class ProfileNotFound extends AppException
{
    public function __construct(string $profileId)
    {
        parent::__construct('Profile not found for id: ' . $profileId);
    }
}
