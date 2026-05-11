<?php

namespace App\Http\Controllers;

use App\Core\Auth\Application\Dto\Request\LoginRequest;
use App\Core\Auth\Application\Dto\Request\RegisterRequest;
use App\Core\Auth\Application\Exception\InvalidCredentialsException;
use App\Core\Auth\Application\UseCase\Login;
use App\Core\Auth\Application\UseCase\Logout;
use App\Core\Auth\Application\UseCase\Me;
use App\Core\Auth\Application\UseCase\RefreshToken;
use App\Core\Auth\Application\UseCase\Register;
use App\Core\Home\House\Model\Service\HouseCreator;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthController extends Controller
{
    public function __construct(
        private Login $login,
        private Register $register,
        private HouseCreator $houseCreator,
        private Logout $logout,
        private RefreshToken $refreshToken,
        private Me $me,
    ) {}

    private function tokenCookie(string $token): Cookie
    {
        return cookie(
            'access_token',
            $token,
            config('jwt.ttl'),
            '/',
            null,
            app()->environment('production'),
            true,
            false,
            'Lax'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $response = $this->login->execute($request);

            return response()
                ->json($response->toArray())
                ->cookie($this->tokenCookie($response->accessToken));

        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $response = $this->register->execute($request);

            $this->houseCreator->create(
                $response->user['id'],
                "{$response->user['name']}s House"
            );

            return response()
                ->json($response->toArray(), 201)
                ->cookie($this->tokenCookie($response->accessToken));

        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->logout->execute();

            return response()
                ->json([
                    'message' => 'Successfully logged out'
                ])
                ->withoutCookie('access_token');

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error logging out'
            ], 500);
        }
    }

    public function refresh(): JsonResponse
    {
        $response = $this->refreshToken->execute();

        return response()
            ->json($response->toArray())
            ->cookie($this->tokenCookie($response->accessToken));
    }

    public function me(): JsonResponse
    {
        return response()->json(
            $this->me->execute()
        );
    }
}