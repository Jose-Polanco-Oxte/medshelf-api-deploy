<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Dto\Request\RegisterRequest;
use App\Core\Auth\Application\Dto\Response\AuthResponse;
use App\Core\Shared\Domain\Utils;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class Register
{
    public function execute(RegisterRequest $request): AuthResponse
    {
        $user = User::create([
            'public_id' => Utils::generateUUIDV4(),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

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
        );
    }
}