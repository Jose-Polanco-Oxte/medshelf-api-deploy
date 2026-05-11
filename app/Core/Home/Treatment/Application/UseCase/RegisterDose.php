<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Item\Application\Dto\Request\ConsumeItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ConsumptionResponse;
use App\Core\Home\Item\Application\UseCase\ConsumeItem;
use App\Core\Home\Treatment\Application\Dto\Request\RegisterDoseRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;

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

        return $this->consumeItem->execute(new ConsumeItemRequest(
            itemId: $treatment->getItemId(),
            amount: $request->amount,
            houseId: $request->houseId,
            treatmentId: $treatment->getId(),
        ));
    }
}
