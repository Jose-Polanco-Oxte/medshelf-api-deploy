<?php

namespace App\Core\Shared\Domain;

final readonly class FloatOperator
{
    private const float DELTA = 0.0001;

    public static function equalTo(float $firstValue, float $lastValue): bool
    {
        return abs($firstValue - $lastValue) < self::DELTA;
    }
}