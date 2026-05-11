<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Exception\LogoutFailedException;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class Logout
{
    public function __construct(private JWTAuth $jwt) {}

    public function execute(): void
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Exception $e) {
            throw new LogoutFailedException();
        }
    }
}
