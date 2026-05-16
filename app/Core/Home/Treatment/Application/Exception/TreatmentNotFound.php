<?php

namespace App\Core\Home\Treatment\Application\Exception;

use App\Core\Shared\Application\NotFoundException;

class TreatmentNotFound extends NotFoundException
{
    public function __construct(string $treatmentId)
    {
        parent::__construct('Treatment not found for id: ' . $treatmentId);
    }
}
