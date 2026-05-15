<?php

namespace App\Core\Home\Profile\Application\Dto\Request;

readonly class AddProfileRequest
{
    public function __construct(
        public string  $userId,
        public string  $name,
        public ?string $relationship,
        public string  $birthDate,
        /** @var string[] */
        public array   $allergies,
    )
    {
    }
}
