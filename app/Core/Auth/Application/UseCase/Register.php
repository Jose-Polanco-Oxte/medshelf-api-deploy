<?php

namespace App\Core\Auth\Application\UseCase;

use App\Core\Auth\Application\Dto\Request\RegisterRequest;
use App\Core\Auth\Application\Dto\Response\AuthResponse;
use App\Core\Home\House\Model\Service\HouseCreator;
use App\Core\Shared\Domain\Utils;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class Register
{
    public function __construct(
        private HouseCreator $houseCreator,
    )
    {
    }

    public function execute(RegisterRequest $request): AuthResponse
    {
        $result = DB::transaction(function () use ($request) {
            $user = User::create([
                'public_id' => Utils::generateUUIDV4(),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]);

            $house = $this->houseCreator->create(
                $user->public_id,
                "{$user->name}s House"
            );

            $token = JWTAuth::fromUser($user->fresh());

            return compact('user', 'house', 'token');
        });

        return new AuthResponse(
            accessToken: $result['token'],
            tokenType: 'Bearer',
            expiresIn: config('jwt.ttl') * 60,
            user: [
                'id' => $result['user']->public_id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
            ]
        );
    }
}