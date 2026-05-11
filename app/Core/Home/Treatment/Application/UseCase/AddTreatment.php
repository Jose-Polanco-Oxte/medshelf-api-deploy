<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Core\Home\Treatment\Application\Dto\Request\AddTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use Carbon\Carbon;

final readonly class AddTreatment
{
    public function __construct(
        private ProfileRepository  $profileRepository,
        private TreatmentRepository $treatmentRepository,
    )
    {
    }

    public function execute(AddTreatmentRequest $request): TreatmentResponse
    {
        $profile = $this->profileRepository->findById($request->profileId)
            ?? throw new ProfileNotFound($request->profileId);

        $treatment = Treatment::create(
            profileId: $profile->getId(),
            itemId: $request->itemId,
            frequencyValue: $request->frequencyValue,
            frequencyUnit: $request->frequencyUnit,
            doseQuantity: $request->doseQuantity,
            startDate: Carbon::parse($request->startDate),
            endDate: $request->endDate ? Carbon::parse($request->endDate) : null,
        );

        $this->treatmentRepository->save($treatment);

        return TreatmentMapper::toResponse($treatment);
    }
}
