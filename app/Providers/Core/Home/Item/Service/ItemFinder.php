<?php

namespace App\Providers\Core\Home\Item\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ItemModel;
use App\Providers\Core\Home\Item\Detail\ItemDetail;
use App\Providers\Core\Home\Item\Resume\PlaceResume;
use App\Providers\Core\Home\Item\Resume\ProductResume;
use App\Providers\Core\Home\Item\View\ItemView;
use App\Providers\Core\Home\Item\Wrapper\NetContent;
use App\Providers\Core\Home\Item\Wrapper\PharmaceuticalForm;
use App\Providers\Core\Home\Item\Wrapper\Product;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class ItemFinder
{
    function findById(string $id): ?ItemDetail
    {
        $record = ItemModel::with([
            'product',
            'storage' => fn($q) => $q->select('id', 'public_id', 'place_id'),
            'storage.place',
        ])
            ->where('public_id', $id)
            ->withSum('consumptions', 'amount')
            ->first();
        if (!$record) {
            return null;
        }
        return $this->toDetail($record);
    }

    private function toDetail(ItemModel $itemModel): ItemDetail
    {
        $product = $itemModel->product;
        $place = $itemModel->storage->place;
        return new ItemDetail(
            id: $itemModel->public_id,
            product: new Product(
                id: $product->public_id,
                name: $product->name,
                netContent: ($product->net_content_value && $product->net_content_unit)
                    ? new NetContent(
                        value: $product->net_content_value,
                        unit: $product->net_content_unit,
                    )
                    : null,
                totalQuantity: $product->total_quantity,
                pharmaceuticalForm: new PharmaceuticalForm(
                    name: $product->pharmaceuticalForm->name,
                    consumptionType: $product->pharmaceuticalForm->consumption_type,
                ),
            ),
            place: new PlaceResume(
                id: $place->public_id,
                name: $place->name,
            ),
            availableContent: $product->pharmaceuticalForm->consumption_type == 'Discrete'
                ? ($itemModel->total_quantity - ($itemModel->consumptions_sum_amount ?? 0))
                : $itemModel->total_content - ($itemModel->consumptions_sum_amount ?? 0),
            expirationDate: $itemModel->expiration_date->toIso8601ZuluString('millisecond'),
            createdAt: $itemModel->created_at->toIso8601ZuluString('millisecond'),
        );
    }

    function listByPlaceIdByOffset(string $placeId, OffsetRequest $request): OffsetResponse
    {
        $result = ItemModel::with([
            'product',
            'storage.place',
        ])
            ->whereHas('storage.place', fn($q) => $q->where('public_id', $placeId))
            ->when(
                $request->filters['name'] ?? null,
                fn($q, $v) => $q->whereHas('product', fn($p) => $p->whereLike('name', "%$v%"))
            )
            ->withSum('consumptions', 'amount')
            ->orderBy('id')
            ->paginate(perPage: $request->size, page: $request->cursor);
        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->cursor,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: collect($result->items())
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    private function toView(ItemModel $itemModel): ItemView
    {
        $product = $itemModel->product;
        return new ItemView(
            id: $itemModel->public_id,
            product: new ProductResume(
                id: $product->public_id,
                name: $product->name,
            ),
            place: new PlaceResume(
                id: $itemModel->storage->place->public_id,
                name: $itemModel->storage->place->name,
            ),
            availableContent: $itemModel->total_content - ($itemModel->consumptions_sum_amount ?? 0),
            expirationDate: $itemModel->expiration_date->toIso8601ZuluString('millisecond'),
        );
    }

    function listByPlaceIdByCursor(string $placeId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ItemModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Item listing.')
        };
        return PaginationService::buildCursorQuery(
            query: ItemModel::with([
                'product',
                'storage.place',
            ])
                ->whereHas('storage.place', fn($q) => $q->where('public_id', $placeId))
                ->when(
                    $request->filters['name'] ?? null,
                    fn($q, $v) => $q->whereHas('product', fn($p) => $p->whereLike('name', "%$v%"))
                )
                ->withSum('consumptions', 'amount')
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }

    function listByHouseIdByOffset(string $houseId, OffsetRequest $request): OffsetResponse
    {
        $result = ItemModel::with([
            'product',
            'storage.place',
        ])
            ->whereHas('storage.place.house', fn($q) => $q->where('public_id', $houseId))
            ->when(
                $request->filters['name'] ?? null,
                fn($q, $v) => $q->whereHas('product', fn($p) => $p->whereLike('name', "%$v%"))
            )
            ->when(
                $request->filters['productId'] ?? null,
                fn($q, $v) => $q->whereHas('product', fn($p) => $p->where('public_id', $v))
            )
            ->withSum('consumptions', 'amount')
            ->orderBy('id')
            ->paginate(perPage: $request->size, page: $request->cursor);
        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->cursor,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: collect($result->items())
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    function listByHouseIdByCursor(string $houseId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ItemModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Item listing.')
        };
        return PaginationService::buildCursorQuery(
            query: ItemModel::with([
                'product',
                'storage.place',
            ])
                ->whereHas('storage.place.house', fn($q) => $q->where('public_id', $houseId))
                ->when(
                    $request->filters['name'] ?? null,
                    fn($q, $v) => $q->whereHas('product', fn($p) => $p->whereLike('name', "%$v%"))
                )
                ->when(
                    $request->filters['productId'] ?? null,
                    fn($q, $v) => $q->whereHas('product', fn($p) => $p->where('public_id', $v))
                )
                ->withSum('consumptions', 'amount')
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
