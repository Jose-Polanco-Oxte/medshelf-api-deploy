<?php

namespace App\Core\Shared\Domain;

interface PaginableByCursor
{
    public function getCursor(): string;
}
