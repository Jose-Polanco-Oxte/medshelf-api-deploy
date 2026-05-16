<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Item\Application\Exception\ProductNotFound;
use App\Core\Home\Item\Model\Repository\ProductRepository;
use App\Core\Home\Item\Model\ValueObject\ConsumptionType;
use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Profile\Model\Repository\ProfileRepository;
use App\Core\Home\Treatment\Application\Dto\Request\CreateTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Response\TreatmentResponse;
use App\Core\Home\Treatment\Application\Mapping\TreatmentMapper;
use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\Treatment;
use Carbon\Carbon;

final readonly class CreateTreatment
{
    public function __construct(
        private ProfileRepository   $profileRepository,
        private TreatmentRepository $treatmentRepository,
        private ProductRepository   $productRepository,
    )
    {
    }

    public function execute(CreateTreatmentRequest $request): TreatmentResponse
    {
        $profile = $this->profileRepository->findById($request->profileId)
            ?? throw new ProfileNotFound($request->profileId);
        $product = $this->productRepository->findById($request->productId)
            ?? throw new ProductNotFound($request->productId);

        if ($product->consumptionType == ConsumptionType::DISCRETE && $request->dose != floor($request->dose)) {
            throw TreatmentException::discreteDoseMustBeInteger($request->dose);
        }
        $treatment = Treatment::create(
            profileId: $profile->getId(),
            productId: $request->productId,
            dose: $request->dose,
            frequencyHours: $request->frequencyHours,
            startDate: Carbon::parse($request->startDate),
            days: $request->days,
        );

        $this->treatmentRepository->save($treatment);

        return TreatmentMapper::toResponse($treatment);
    }
}
