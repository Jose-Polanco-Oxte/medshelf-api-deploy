<?php

namespace App\Core\Shared\Application;

/**
 * Thrown when a requested resource does not exist.
 *
 * The central exception renderer maps this to HTTP 404.
 * All domain-level "not found" exceptions extend this class.
 */
class NotFoundException extends AppException
{
}
