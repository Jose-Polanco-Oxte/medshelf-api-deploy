<?php

namespace App\Http\Controllers;

use App\Core\Auth\Application\Dto\Request\LoginRequest;
use App\Core\Auth\Application\Dto\Request\RegisterRequest;
use App\Core\Auth\Application\UseCase\Login;
use App\Core\Auth\Application\UseCase\Logout;
use App\Core\Auth\Application\UseCase\Me;
use App\Core\Auth\Application\UseCase\RefreshToken;
use App\Core\Auth\Application\UseCase\Register;
use Exception;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

final class AuthController extends Controller
{
    public function __construct(
        private Login        $login,
        private Register     $register,
        private Logout       $logout,
        private RefreshToken $refreshToken,
        private Me           $me,
    )
    {
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Auth"},
     *     summary="Start session",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=8)
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AuthResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->login->execute($request);

        return response()
            ->json($response->toArray())
            ->cookie($this->tokenCookie($response->accessToken));
    }

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
            'none'
        );
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Auth"},
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Maria"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=5)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/AuthResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->register->execute($request);

        return response()
            ->json($response->toArray(), 201)
            ->cookie($this->tokenCookie($response->accessToken));
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Close session",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=500, description="Server error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Auth"},
     *     summary="Refresh token",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AuthResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function refresh(): JsonResponse
    {
        $response = $this->refreshToken->execute();

        return response()
            ->json($response->toArray())
            ->cookie($this->tokenCookie($response->accessToken));
    }

    /**
     * @OA\Get(
     *     path="/auth/account",
     *     tags={"Auth"},
     *     summary="Get authenticated user data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AccountResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function account(): JsonResponse
    {
        return response()->json(
            $this->me->execute()
        );
    }

    /**
     * @OA\Delete(
     *     path="/auth/account",
     *     tags={"Auth"},
     *     summary="Soft-delete the authenticated user account",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function deleteAccount(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        JWTAuth::parseToken()->invalidate();
        $user->delete();

        return response()
            ->json(['message' => 'Account deleted successfully'])
            ->withoutCookie('access_token');
    }

    /**
     * @OA\Patch(
     *     path="/auth/account",
     *     tags={"Auth"},
     *     summary="Update authenticated user data",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="Maria"),
     *             @OA\Property(property="email", type="string", format="email", example="jose@gmail.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AccountResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function updateAccount(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user->name = request('name', $user->name);
        $user->email = request('email', $user->email);
        $user->save();
        return response()
            ->json([
                'id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }
}