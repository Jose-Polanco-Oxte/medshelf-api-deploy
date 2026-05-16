<?php

namespace App\Providers\Core\Home\Profile\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ProfileModel;
use App\Providers\Core\Home\Profile\Detail\ProfileDetail;
use App\Providers\Core\Home\Profile\View\ProfileView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class ProfileFinder
{
    public function findById(string $id): ?ProfileDetail
    {
        $record = ProfileModel::with([
            'user' => fn($q) => $q->select('id', 'public_id'),
            'allergies' => fn($q) => $q->select('id', 'profile_id', 'name'),
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;

        return $this->toDetail($record);
    }

    private function toDetail(ProfileModel $record): ProfileDetail
    {
        return new ProfileDetail(
            id: $record->public_id,
            name: $record->name,
            relationship: $record->relationship,
            birthDate: $record->birthdate,
            allergies: $record->allergies->pluck('name')->toArray(),
            createdAt: $record->created_at,
        );
    }

    public function listByUserIdByOffset(string $userId, OffsetRequest $request): OffsetResponse
    {
        $result = ProfileModel::with([
            'allergies' => fn($q) => $q->select('id', 'profile_id', 'name'),
        ])
            ->whereHas('user', fn($q) => $q->where('public_id', $userId))
            ->when($request->filters ?? [], fn($q, $filters) => $q->where(function ($q) use ($filters) {
                if (isset($filters['name'])) {
                    $q->where('name', 'like', '%' . $filters['name'] . '%');
                }
            }))
            ->orderBy('created_at', 'desc')
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
            birthDate: $record->birthdate?->toIso8601ZuluString('millisecond'),
            allergies: $record->allergies->pluck('name')->toArray(),
        );
    }

    public function listByUserIdByCursor(string $userId, CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ProfileModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Profile listing.')
        };

        return PaginationService::buildCursorQuery(
            query: ProfileModel::with([
                'allergies' => fn($q) => $q->select('id', 'profile_id', 'name'),
            ])
                ->whereHas('user', fn($q) => $q->where('public_id', $userId))
                ->when($request->filters ?? [], fn($q, $filters) => $q->where(function ($q) use ($filters) {
                    if (isset($filters['name'])) {
                        $q->where('name', 'like', '%' . $filters['name'] . '%');
                    }
                }))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
