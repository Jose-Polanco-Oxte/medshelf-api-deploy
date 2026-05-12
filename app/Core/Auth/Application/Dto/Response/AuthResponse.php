<?php

namespace App\Core\Auth\Application\Dto\Response;

readonly class AuthResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public array $user,
    ) {}

    public function toArray(): array
    {
        return [
            'expires_in' => $this->expiresIn,
            'user' => $this->user,
        ];
    }
}