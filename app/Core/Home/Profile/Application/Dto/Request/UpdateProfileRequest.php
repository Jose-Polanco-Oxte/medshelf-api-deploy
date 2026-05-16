<?php

namespace App\Core\Home\Profile\Application\Dto\Request;

readonly class UpdateProfileRequest
{
    public function __construct(
        public string  $profileId,
        public ?string $name,
        public ?string $relationship,
        public ?array  $allergies,
    )
    {
    }
}
