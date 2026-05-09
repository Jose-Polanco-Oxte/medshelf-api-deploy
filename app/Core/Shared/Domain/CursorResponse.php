<?php

namespace App\Core\Shared\Domain;

readonly class CursorResponse
{
    public function __construct(
        public ?string $nextCursor,
        public array   $items = []
    )
    {
    }
}
