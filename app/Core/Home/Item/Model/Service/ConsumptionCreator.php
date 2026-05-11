<?php

namespace App\Core\Home\Item\Model\Service;

use App\Core\Home\Item\Model\Consumption;
use App\Core\Home\Item\Model\Exception\ConsumptionException;
use App\Core\Home\Item\Model\Repository\ConsumptionRepository;
use App\Core\Home\Item\Model\Repository\ItemRepository;
use App\Core\Home\Item\Model\Repository\ProductRepository;

final readonly class ConsumptionCreator
{
    public function __construct(
        private ItemRepository        $itemRepository,
        private ProductRepository     $productRepository,
        private ConsumptionRepository $consumptionRepository,
        private ItemPolicy            $itemPolicy,
    )
    {
    }

    public function consume(string $itemId, string $houseId, float $amount, ?string $treatmentId = null): Consumption
    {
        $item = $this->itemRepository->findByIdAndHouseId($itemId, $houseId) ??
            throw ConsumptionException::itemNotFound($itemId);
        $product = $this->productRepository->findById($item->getProductId()) ??
            throw ConsumptionException::productNotFound($item->getProductId());
        $this->itemPolicy->assertConsumption($item, $amount, $product);
        $consumption = Consumption::create(
            itemId: $item->getId(),
            amount: $amount,
            treatmentId: $treatmentId,
        );
        $this->consumptionRepository->consume($consumption);
        return $consumption;
    }
}