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
            itemId: $treatment->getItemId(),
            status: $treatment->getStatus()->value,
            frequencyValue: $treatment->getFrequencyValue(),
            frequencyUnit: $treatment->getFrequencyUnit(),
            doseQuantity: $treatment->getDoseQuantity(),
            startDate: $treatment->getStartDate(),
            endDate: $treatment->getEndDate(),
            createdAt: $treatment->getCreatedAt(),
        );
    }
}
