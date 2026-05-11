<?php

namespace App\Providers\Core\Home\Profile\View;

use App\Core\Shared\Domain\PaginableByCursor;

readonly class ProfileView implements PaginableByCursor
{
    public function __construct(
        public string  $id,
        public string  $name,
        public ?string $relationship,
    )
    {
    }

    public function getCursor(): string
    {
        return $this->id;
    }
}
