<?php

namespace App\Core\Home\Item\Application\Dto\Request;

readonly class ConsumeItemRequest
{
    public function __construct(
        public string  $itemId,
        public float   $amount,
        public string  $houseId,
        public ?string $treatmentId = null,
    )
    {
    }
}
