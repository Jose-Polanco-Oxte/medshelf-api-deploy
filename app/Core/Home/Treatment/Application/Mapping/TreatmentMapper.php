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
            profile: ['id' => $treatment->getProfileId()],
            item: ['id' => $treatment->getItemId()],
            status: $treatment->getStatus()->value,
            frequencyValue: $treatment->getFrequencyValue(),
            frequencyUnit: $treatment->getFrequencyUnit(),
            doseQuantity: $treatment->getDoseQuantity(),
            startDate: $treatment->getStartDate()->toDateString(),
            endDate: $treatment->getEndDate()?->toDateString(),
            createdAt: $treatment->getCreatedAt()->toIso8601String(),
        );
    }
}
