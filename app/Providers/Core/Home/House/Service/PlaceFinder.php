<?php

namespace App\Providers\Core\Home\House\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\PlaceModel;
use App\Providers\Core\Home\House\Detail\PlaceDetail;
use App\Providers\Core\Home\House\Resume\HouseResume;
use App\Providers\Core\Home\House\View\PlaceView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class PlaceFinder
{
    public function findById(string $id): ?PlaceDetail
    {
        $record = PlaceModel::with('house')
            ->where('public_id', $id)
            ->first();
        if (!$record) return null;
        return $this->toDetail($record);
    }

    private function toDetail(PlaceModel $record): PlaceDetail
    {
        $house = $record->house;
        return new PlaceDetail(
            id: $record->public_id,
            house: new HouseResume(
                id: $house->public_id,
                name: $house->name,
            ),
            name: $record->name,
            createdAt: $record->created_at->toIso8601ZuluString('millisecond'),
        );
    }

    public function listByHouseIdByOffset(string $houseId, OffsetRequest $request): OffsetResponse
    {
        $result = PlaceModel::whereHas('house', fn($q) => $q->where('public_id', $houseId))
            ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"))
            ->orderBy('id')
            ->paginate(perPage: $request->size, page: $request->page);
        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->cursor,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: $result->getCollection()
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    private function toView(PlaceModel $record): PlaceView
    {
        return new PlaceView(
            id: $record->public_id,
            name: $record->name,
        );
    }

    public function listByHouseIdByCursor(string $houseId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => PlaceModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Place listing.')
        };

        return PaginationService::buildCursorQuery(
            query: PlaceModel::whereHas('house', fn($q) => $q->where('public_id', $houseId))
                ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
