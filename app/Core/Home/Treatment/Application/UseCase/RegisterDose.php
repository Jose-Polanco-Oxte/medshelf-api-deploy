<?php

namespace App\Core\Home\Treatment\Application\UseCase;

use App\Core\Home\Item\Application\Dto\Request\ConsumeItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ConsumptionResponse;
use App\Core\Home\Item\Application\Exception\ItemNotFound;
use App\Core\Home\Item\Application\UseCase\ConsumeItem;
use App\Core\Home\Item\Model\Exception\ConsumptionException;
use App\Core\Home\Item\Model\Repository\ItemRepository;
use App\Core\Home\Treatment\Application\Dto\Request\RegisterDoseRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Home\Treatment\Model\Repository\TreatmentRepository;

final readonly class RegisterDose
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
        private ItemRepository      $itemRepository,
        private ConsumeItem         $consumeItem,
    )
    {
    }

    public function execute(RegisterDoseRequest $request): ConsumptionResponse
    {
        $treatment = $this->treatmentRepository->findById($request->treatmentId)
            ?? throw new TreatmentNotFound($request->treatmentId);

        $treatment->assertCanRegisterDose();

        $item = $this->itemRepository->findByIdAndHouseId($request->itemId, $request->houseId)
            ?? throw new ItemNotFound($request->itemId);

        if ($item->getProductId() !== $treatment->getProductId()) {
            throw TreatmentException::itemDoesNotBelongToProduct($request->itemId, $treatment->getProductId());
        }

        try {
            $consumption = $this->consumeItem->execute(new ConsumeItemRequest(
                itemId: $request->itemId,
                amount: $request->amount,
                houseId: $request->houseId,
                treatmentId: $treatment->getId(),
            ));
        } catch (ConsumptionException $e) {
            throw TreatmentException::cannotConsumeDose($e);
        }
        return $consumption;
    }
}
