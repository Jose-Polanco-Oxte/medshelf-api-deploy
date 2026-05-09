<?php

namespace App\Providers\Core\Catalog\Product\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ActiveIngredientModel;
use App\Providers\Core\Catalog\Product\View\ActiveIngredientView;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class ActiveIngredientFinder
{
    public function listByOffset(OffsetRequest $request): OffsetResponse
    {
        $result = ActiveIngredientModel::orderBy('id')
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

    private function toView(ActiveIngredientModel $record): ActiveIngredientView
    {
        return new ActiveIngredientView(
            id: $record->id,
            name: $record->name,
        );
    }

    public function listByCursor(CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ActiveIngredientModel::where('id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Active Ingredient listing.')
        };

        return PaginationService::buildCursorQuery(
            query: ActiveIngredientModel::orderBy('id')
                ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%")),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
