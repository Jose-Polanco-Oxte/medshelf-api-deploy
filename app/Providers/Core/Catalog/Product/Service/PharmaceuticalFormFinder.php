<?php

namespace App\Providers\Core\Catalog\Product\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\PharmaceuticalFormModel;
use App\Providers\Core\Catalog\Product\View\PharmaceuticalFormView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class PharmaceuticalFormFinder
{
    public function listByOffset(OffsetRequest $request): OffsetResponse
    {
        $result = PharmaceuticalFormModel::orderBy('id')
            ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"))
            ->paginate(perPage: $request->size, page: $request->page);

        return new OffsetResponse(
            totalCount: $result->total(),
            page: $request->page,
            size: $request->size,
            hasMorePages: $result->hasMorePages(),
            items: $result->getCollection()
                ->map(fn($item) => $this->toView($item))
                ->toArray()
        );
    }

    private function toView(PharmaceuticalFormModel $record): PharmaceuticalFormView
    {
        return new PharmaceuticalFormView(
            id: $record->id,
            name: $record->name,
            consumptionType: $record->consumption_type,
        );
    }

    public function listByCursor(CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => PharmaceuticalFormModel::where('id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Pharmaceutical Form listing.')
        };
        $query = PharmaceuticalFormModel::orderBy('id')
            ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"));


        return PaginationService::buildCursorQuery(
            query: $query,
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
