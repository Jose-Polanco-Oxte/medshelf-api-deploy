<?php

namespace App\Core\Home\Treatment\Application\Dto\Request;

readonly class TreatmentActionRequest
{
    public function __construct(
        public string $treatmentId,
    )
    {
    }
}
