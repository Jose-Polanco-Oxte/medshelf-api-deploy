<?php

namespace App\Core\Shared\Domain;

readonly class OffsetRequest
{
    public function __construct(
        public int $page = 1,
        public int $size = 10
    )
    {
    }
}
