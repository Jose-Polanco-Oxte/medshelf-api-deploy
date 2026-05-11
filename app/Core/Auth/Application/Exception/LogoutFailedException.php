<?php

namespace App\Core\Auth\Application\Exception;

use App\Core\Shared\Application\AppException;

final class LogoutFailedException extends AppException
{
    public function __construct()
    {
        parent::__construct('Logout failed', 500);
    }
}
