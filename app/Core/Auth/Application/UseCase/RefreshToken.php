<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Dto\Response\AuthResponse;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class RefreshToken
{
    public function execute(): AuthResponse
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        $user = Auth::user();

        return new AuthResponse(
            accessToken: $newToken,
            tokenType: 'Bearer',
            expiresIn: config('jwt.ttl') * 60,
            user: [
                'id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        );
    }
}