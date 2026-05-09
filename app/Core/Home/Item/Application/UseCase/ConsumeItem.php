<?php

namespace App\Core\Home\Item\Application\UseCase;

use App\Core\Home\Item\Application\Dto\Request\ConsumeItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ConsumptionResponse;
use App\Core\Home\Item\Application\Mapping\ItemMapper;
use App\Core\Home\Item\Model\Service\ConsumptionCreator;
use App\Core\Shared\Application\EventPublisher;

final readonly class ConsumeItem
{
    public function __construct(
        private ConsumptionCreator $consumptionCreator,
        private EventPublisher     $eventPublisher,
    )
    {
    }

    public function execute(ConsumeItemRequest $request): ConsumptionResponse
    {
        $consumption = $this->consumptionCreator->consume(
            itemId: $request->itemId,
            houseId: $request->houseId,
            amount: $request->amount,
            treatmentId: $request->treatmentId,
        );
        $this->eventPublisher->publish(...$consumption->pullEvents());
        return ItemMapper::toConsumptionResponse($consumption);
    }
}
