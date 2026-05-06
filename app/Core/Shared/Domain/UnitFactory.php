<?php

namespace App\Core\Shared\Domain;

use InvalidArgumentException;

final readonly class UnitFactory
{
    public static function create(string $name): Unit
    {
        return match ($name) {
            'mcg' => Mass::MCG,
            'g' => Mass::G,
            'mg' => Mass::MG,
            'mcl' => Volume::MCL,
            'l' => Volume::L,
            'ml' => Volume::ML,
            default => throw new InvalidArgumentException("Unsupported type: $name"),
        };
    }
}