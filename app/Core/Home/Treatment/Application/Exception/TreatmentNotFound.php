<?php

namespace App\Core\Home\Treatment\Application\Exception;

use App\Core\Shared\Application\AppException;

class TreatmentNotFound extends AppException
{
    public function __construct(string $treatmentId)
    {
        parent::__construct('Treatment not found for id: ' . $treatmentId);
    }
}
