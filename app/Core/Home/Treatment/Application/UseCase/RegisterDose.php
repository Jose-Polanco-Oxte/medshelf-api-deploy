<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Item\Application\Dto\Request\ConsumeItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ConsumptionResponse;
use App\Core\Home\Item\Application\UseCase\ConsumeItem;
use App\Core\Home\Item\Model\Exception\ConsumptionException;
use App\Core\Home\Treatment\Application\Dto\Request\RegisterDoseRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;
use App\Core\Home\Treatment\Model\TreatmentStatus;

final readonly class RegisterDose
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
        private ConsumeItem         $consumeItem,
    )
    {
    }

    public function execute(RegisterDoseRequest $request): ConsumptionResponse
    {
        $treatment = $this->treatmentRepository->findById($request->treatmentId)
            ?? throw new TreatmentNotFound($request->treatmentId);

        $treatment->assertCanRegisterDose();

        try {
            $consumption = $this->consumeItem->execute(new ConsumeItemRequest(
                itemId: $treatment->getItemId(),
                amount: $request->amount,
                houseId: $request->houseId,
                treatmentId: $treatment->getId(),
            ));
        } catch (ConsumptionException $e) {
            $treatment->changeStatus(TreatmentStatus::PAUSED);
            $this->treatmentRepository->save($treatment);
            throw TreatmentException::cannotConsumeDose($e);
        }
        return $consumption;
    }
}
