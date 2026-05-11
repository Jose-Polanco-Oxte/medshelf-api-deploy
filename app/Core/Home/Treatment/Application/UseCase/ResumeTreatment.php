<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Treatment\Application\Dto\Request\TreatmentActionRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;

final readonly class ResumeTreatment
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
    )
    {
    }

    public function execute(TreatmentActionRequest $request): TreatmentResponse
    {
        $treatment = $this->treatmentRepository->findById($request->treatmentId)
            ?? throw new TreatmentNotFound($request->treatmentId);

        $treatment->resume();

        $this->treatmentRepository->save($treatment);

        return TreatmentMapper::toResponse($treatment);
    }
}
