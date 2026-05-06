<?php

namespace App\Core\Shared\Domain;

interface Unit
{
    public function convert(float $value): float;

    public function symbol(): string;
}