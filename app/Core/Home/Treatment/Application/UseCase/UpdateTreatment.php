<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;

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

        $treatment->changeDose($request->dose);
        $treatment->changeFrequencyHours($request->frequencyHours);
        $treatment->changeDays($request->days);
        $treatment->changeStatus(TreatmentStatus::from($request->status));
        $this->treatmentRepository->save($treatment);
        return TreatmentMapper::toResponse($treatment);
    }
}
