<?php

namespace App\Providers\Core\Home\Profile\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ProfileModel;
use App\Providers\Core\Home\Profile\Detail\ProfileDetail;
use App\Providers\Core\Home\Profile\View\ProfileView;
use App\Services\PaginationService;
use Illuminate\Pagination\Cursor;

class ProfileFinder
{
    public function findById(string $id): ?ProfileDetail
    {
        $record = ProfileModel::with(['user' => fn($q) => $q->select('id', 'public_id')])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDetail($record);
    }

    private function toDetail(ProfileModel $record): ProfileDetail
    {
        return new ProfileDetail(
            id: $record->public_id,
            userId: $record->user->public_id,
            name: $record->name,
            relationship: $record->relationship,
            createdAt: $record->created_at,
        );
    }

    public function listByUserIdByOffset(string $userId, OffsetRequest $request): OffsetResponse
    {
        $result = ProfileModel::whereHas('user', fn($q) => $q->where('public_id', $userId))
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

    private function toView(ProfileModel $record): ProfileView
    {
        return new ProfileView(
            id: $record->public_id,
            name: $record->name,
            relationship: $record->relationship,
        );
    }

    public function listByUserIdByCursor(string $userId, CursorRequest $request): CursorResponse
    {
        $id = $request->cursor
            ? ProfileModel::where('public_id', $request->cursor)->value('id')
            : null;

        return PaginationService::buildCursorQuery(
            query: ProfileModel::whereHas('user', fn($q) => $q->where('public_id', $userId))
                ->orderBy('id'),
            cursor: $id ? new Cursor(['id' => $id]) : null,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
