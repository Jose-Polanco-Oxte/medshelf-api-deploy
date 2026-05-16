<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class RegisterDoseRequest
{
    public function __construct(
        public string $treatmentId,
        public string $itemId,
        public float  $amount,
        public string $houseId,
    )
    {
    }
}
