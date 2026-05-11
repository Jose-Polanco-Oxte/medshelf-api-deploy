<?php

namespace App\Core\Auth\Application\Exception;

use App\Core\Shared\Application\AppException;

final class InvalidCredentialsException extends AppException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials', 401);
    }
}
