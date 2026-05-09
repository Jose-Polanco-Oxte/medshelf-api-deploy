<?php

namespace App\Core\Shared\Domain;

readonly class OffsetResponse
{
    public function __construct(
        public int   $totalCount,
        public int   $page,
        public int   $size,
        public bool  $hasMorePages,
        public array $items = []
    )
    {
    }
}
