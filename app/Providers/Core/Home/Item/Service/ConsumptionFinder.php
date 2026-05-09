<?php

namespace App\Providers\Core\Home\Item\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ConsumptionModel;
use App\Providers\Core\Home\Item\Detail\ConsumptionDetail;
use App\Providers\Core\Home\Item\Resume\ItemResume;
use App\Providers\Core\Home\Item\Resume\ProductResume;
use App\Providers\Core\Home\Item\View\ConsumptionView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class ConsumptionFinder
{
    public function findById(string $id): ?ConsumptionDetail
    {
        $record = ConsumptionModel::with([
            'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
            'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;
        return $this->toDetail($record);
    }

    private function toDetail(ConsumptionModel $record): ConsumptionDetail
    {
        $item = $record->item;
        $product = $item->product;
        return new ConsumptionDetail(
            id: $record->public_id,
            item: new ItemResume(
                id: $item->public_id,
                product: new ProductResume(
                    id: $product->public_id,
                    name: $product->name,
                )
            ),
            amount: $record->amount,
            consumedAt: $record->consumed_at,
        );
    }

    public function listByItemIdByOffset(string $itemId, OffsetRequest $request): OffsetResponse
    {
        $result = ConsumptionModel::whereHas('item', fn($q) => $q->where('public_id', $itemId))
            ->orderBy('id')
            ->paginate(perPage: $request->size, page: $request->page);

        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->page,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: collect($result->items())
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    private function toView(ConsumptionModel $consumptionModel): ConsumptionView
    {
        return new ConsumptionView(
            id: $consumptionModel->public_id,
            amount: $consumptionModel->amount,
            consumedAt: $consumptionModel->consumed_at,
        );
    }

    public function listByItemIdByCursor(string $itemId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ConsumptionModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Consumption listing.')
        };

        return PaginationService::buildCursorQuery(
            query: ConsumptionModel::whereHas('item', fn($q) => $q->where('public_id', $itemId))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }

    public function listByTreatmentIdByOffset(string $treatmentId, OffsetRequest $request): OffsetResponse
    {
        $result = ConsumptionModel::whereHas('treatment', fn($q) => $q->where('public_id', $treatmentId))
            ->orderBy('id')
            ->paginate(perPage: $request->size, page: $request->page);

        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->page,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: collect($result->items())
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    public function listByTreatmentIdByCursor(string $treatmentId, CursorRequest $request): CursorResponse
    {
        $id = $request->cursor
            ? ConsumptionModel::where('public_id', $request->cursor)->value('id')
            : null;

        return PaginationService::buildCursorQuery(
            query: ConsumptionModel::whereHas('treatment', fn($q) => $q->where('public_id', $treatmentId))
                ->orderBy('id'),
            cursor: $id ? new Cursor(['id' => $id]) : null,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
