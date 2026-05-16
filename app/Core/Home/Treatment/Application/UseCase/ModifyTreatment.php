<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Treatment\Application\Dto\Request\ModifyTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\TreatmentStatus;
use Carbon\Carbon;

final readonly class ModifyTreatment
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
    )
    {
    }

    public function execute(ModifyTreatmentRequest $request): TreatmentResponse
    {
        $treatment = $this->treatmentRepository->findById($request->treatmentId)
            ?? throw new TreatmentNotFound($request->treatmentId);

        $treatment->changeDose($request->dose);
        $treatment->changeFrequencyUnit($request->frequencyUnit);
        $treatment->changeEndDate($request->endDate ? Carbon::parse($request->endDate) : null);
        $treatment->changeStatus(TreatmentStatus::from($request->status));
        $this->treatmentRepository->save($treatment);
        return TreatmentMapper::toResponse($treatment);
    }
}
