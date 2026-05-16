<?php

namespace App\Core\Home\Treatment\Application\Mapping;

use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Model\Treatment;

final class TreatmentMapper
{
    private function __construct()
    {
    }

    public static function toResponse(Treatment $treatment): TreatmentResponse
    {
        return new TreatmentResponse(
            id: $treatment->getId(),
            profileId: $treatment->getProfileId(),
            productId: $treatment->getProductId(),
            status: $treatment->getStatus()->value,
            dose: $treatment->getDose(),
            frequencyHours: $treatment->getFrequencyHours(),
            startDate: $treatment->getStartDate(),
            days: $treatment->getDays(),
            createdAt: $treatment->getCreatedAt(),
        );
    }
}
