<?php

use App\Core\Shared\Application\AppException;
use App\Core\Shared\Domain\DomainException;
use App\Http\Middleware\AuthenticateApi;
use App\Providers\Core\InfrastructureException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\AuthenticateApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (DomainException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 400);
        });

        $exceptions->renderable(function (AppException $e, Request $request) {
            return response()->json([
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 409);
        });

        $exceptions->renderable(function (InfrastructureException $e, Request $request) {
            $payload = [
                'message' => 'An unexpected error has occurred',
                'timestamp' => now()->toIso8601String(),
            ];

            if (config('app.debug')) {
                $payload['details'] = $e->getMessage();
            }

            return response()->json($payload, 500);
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'timestamp' => now()->toIso8601String(),
            ], 422);
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'message' => 'Authentication required',
                'timestamp' => now()->toIso8601String(),
            ], 401);
        });

        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            return response()->json([
                'message' => 'Forbidden',
                'timestamp' => now()->toIso8601String(),
            ], 403);
        });

        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();
            $message = match ($status) {
                404 => 'Resource not found',
                405 => 'Method not allowed',
                401 => 'Authentication required',
                403 => 'Forbidden',
                default => 'HTTP error',
            };

            return response()->json([
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ], $status);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $payload = [
                'message' => 'An unexpected error occurred',
                'timestamp' => now()->toIso8601String(),
            ];

            if (config('app.debug')) {
                $payload['details'] = $e->getMessage();
            }

            return response()->json($payload, 500);
        });
    })->create();
