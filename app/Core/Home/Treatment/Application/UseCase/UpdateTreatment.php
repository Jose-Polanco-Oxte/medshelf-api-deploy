<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;

final readonly class UpdateTreatment
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
    )
    {
    }

    public function execute(UpdateTreatmentRequest $request): TreatmentResponse
    {
        $treatment = $this->treatmentRepository->findById($request->treatmentId)
            ?? throw new TreatmentNotFound($request->treatmentId);

        $treatment->update(
            frequencyValue: $request->frequencyValue,
            frequencyUnit: $request->frequencyUnit,
            doseQuantity: $request->doseQuantity,
            endDate: $request->endDate,
        );

        $this->treatmentRepository->save($treatment);

        return TreatmentMapper::toResponse($treatment);
    }
}
