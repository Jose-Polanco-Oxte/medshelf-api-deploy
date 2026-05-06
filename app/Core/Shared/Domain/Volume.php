<?php

namespace App\Core\Shared\Domain;

enum Volume: string implements Unit
{
    case L = 'l';
    case ML = 'ml';
    case MCL = 'mcl';

    public function convert(float $value): float
    {
        return match ($this) {
            self::L => $value,
            self::ML => $value / 1000,
            self::MCL => $value / 1_000_000,
        };
    }

    public function symbol(): string
    {
        return $this->value;
    }
}
