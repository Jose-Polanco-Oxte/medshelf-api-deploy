<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Dto\Response\AuthResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class RefreshToken
{
    public function __construct(private JWTAuth $jwt) {}

    public function execute(): AuthResponse
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        $user = Auth::user();
        $ttl = config('jwt.ttl') * 60;

        return new AuthResponse(
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $ttl,
            user: [
                'id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        );
    }
}
