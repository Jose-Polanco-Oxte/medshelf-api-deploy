<?php

namespace App\Providers\Core\Home\Treatment\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\TreatmentModel;
use App\Providers\Core\Home\Item\Resume\ItemResume;
use App\Providers\Core\Home\Item\Resume\ProductResume;
use App\Providers\Core\Home\Treatment\Detail\TreatmentDetail;
use App\Providers\Core\Home\Treatment\Resume\ProfileResume;
use App\Providers\Core\Home\Treatment\View\TreatmentView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class TreatmentFinder
{
    public function findById(string $id): ?TreatmentDetail
    {
        $record = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id', 'name'),
            'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
            'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDetail($record);
    }

    private function toDetail(TreatmentModel $record): TreatmentDetail
    {
        return new TreatmentDetail(
            id: $record->public_id,
            profile: new ProfileResume(
                id: $record->profile->public_id,
                name: $record->profile->name,
            ),
            item: new ItemResume(
                id: $record->item->public_id,
                product: new ProductResume(
                    id: $record->item->public_id,
                    name: $record->item->product->name,
                )
            ),
            status: $record->status,
            dose: $record->dose,
            frequencyHours: $record->frequency_hours,
            startDate: $record->start_date,
            days: $record->days,
            createdAt: $record->created_at,
        );
    }

    public function listByProfileIdByOffset(string $profileId, OffsetRequest $request): OffsetResponse
    {
        $result = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id', 'name'),
            'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
            'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
        ])
            ->whereHas('profile', fn($q) => $q->where('public_id', $profileId))
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

    private function toView(TreatmentModel $record): TreatmentView
    {
        return new TreatmentView(
            id: $record->public_id,
            profile: new ProfileResume(
                id: $record->profile->public_id,
                name: $record->profile->name,
            ),
            item: new ItemResume(
                id: $record->item->public_id,
                product: new ProductResume(
                    $record->item->product->public_id,
                    $record->item->product->name,
                )
            ),
            status: $record->status,
            dose: $record->dose,
            frequencyHours: $record->frequency_hours,
            startDate: $record->start_date->toDateString(),
            days: $record->days,
        );
    }

    public function listByProfileIdByCursor(string $profileId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => TreatmentModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Treatment listing.')
        };

        return PaginationService::buildCursorQuery(
            query: TreatmentModel::with([
                'profile' => fn($q) => $q->select('id', 'public_id', 'name'),
                'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
                'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
            ])
                ->whereHas('profile', fn($q) => $q->where('public_id', $profileId))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }

    public function listByUserIdByCursor(string $userId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => TreatmentModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Treatment listing.')
        };

        return PaginationService::buildCursorQuery(
            query: TreatmentModel::with([
                'profile' => fn($q) => $q->select('id', 'public_id', 'name'),
                'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
                'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
            ])
                ->whereHas('profile.user', fn($q) => $q->where('public_id', $userId))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }

    public function listByUserIdByOffset(string $userId, OffsetRequest $request): OffsetResponse
    {
        $result = TreatmentModel::with([
            'profile' => fn($q) => $q->select('id', 'public_id', 'name'),
            'item' => fn($q) => $q->select('id', 'public_id', 'product_id'),
            'item.product' => fn($q) => $q->select('id', 'public_id', 'name'),
        ])
            ->whereHas('profile.user', fn($q) => $q->where('public_id', $userId))
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
}
