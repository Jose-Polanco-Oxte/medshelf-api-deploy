<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Dto\Request\LoginRequest;
use App\Core\Auth\Application\Dto\Response\AuthResponse;
use App\Core\Auth\Application\Exception\InvalidCredentialsException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class Login
{
    public function execute(LoginRequest $request): AuthResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            throw new InvalidCredentialsException();
        }

        $user = Auth::user();

        $token = JWTAuth::fromUser($user);

        return new AuthResponse(
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: config('jwt.ttl') * 60,
            user: [
                'id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            house: $user->house
                ? ['id' => $user->house->public_id, 'name' => $user->house->name]
                : null,
        );
    }
}