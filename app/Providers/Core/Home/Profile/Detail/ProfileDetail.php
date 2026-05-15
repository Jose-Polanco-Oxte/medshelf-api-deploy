<?php

namespace App\Providers\Core\Home\Profile\Detail;

use Carbon\Carbon;

readonly class ProfileDetail
{
    public function __construct(
        public string  $id,
        public string  $name,
        public ?string $relationship,
        public Carbon  $birthDate,
        /** @var string[] */
        public array   $allergies,
        public Carbon  $createdAt,
    )
    {
    }
}
