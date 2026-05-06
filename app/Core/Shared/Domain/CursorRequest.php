<?php

namespace App\Core\Shared\Domain;

readonly class CursorRequest
{
    public function __construct(
        public ?string $cursor = null,
        public int     $size = 10
    )
    {
    }
}
