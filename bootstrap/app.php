<?php

use App\Core\Shared\Application\AppException;
use App\Core\Shared\Domain\DomainException;
use App\Http\Middleware\AuthenticateApi;
use App\Providers\Core\InfrastructureException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof DomainException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 400);
            } else if ($e instanceof AppException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 409);
            } else if ($e instanceof InfrastructureException) {
                return response()->json([
                    'message' => 'An unexpected error has occurred',
                    'details' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 500);
            } else if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'timestamp' => now()->toIso8601String(),
                ], 422);
            } else {
                return response()->json([
                    'message' => 'An unexpected error occurred',
                    'timestamp' => now()->toIso8601String(),
                ], 500);
            }
        });
    })->create();
