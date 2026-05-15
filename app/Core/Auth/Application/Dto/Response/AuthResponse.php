<?php

namespace App\Core\Auth\Application\Dto\Response;

readonly class AuthResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int    $expiresIn,
        public array  $user,
        public ?array $house,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user,
            'expiresIn' => $this->expiresIn,
            'house' => $this->house,
        ];
    }
}