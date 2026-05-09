<?php

namespace App\Providers\Core\Home\Treatment\Resume;

readonly class ProfileResume
{
    public function __construct(
        public string $id,
        public string $name,
    )
    {
    }
}
