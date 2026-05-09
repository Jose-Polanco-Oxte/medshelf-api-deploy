<?php

namespace App\Services;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Core\Shared\Domain\PaginableByCursor;
use App\Http\Requests\ListRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Cursor;

final class PaginationService
{
    private function __construct()
    {
    }

    // :V
    public static function paginate(
        ListRequest $request,
        callable    $dataFetcherByCursor,
        callable    $dataFetcherByOffset,
        ?callable   $itemFormatter = null
    ): JsonResponse
    {
        if ($request->has('cursor') || !$request->has('page')) {
            $cursorRequest = new CursorRequest(
                cursor: $request->string('cursor')->toString() ?: null,
                size: $request->integer('size', 10),
                filters: $request->query('filter') ?? [],
            );
            $result = $dataFetcherByCursor($cursorRequest);
            if (!$result instanceof CursorResponse) {
                return response()->json(['message' => 'Invalid response from cursor data fetcher.'], 500);
            }
            $items = $result->items;
            if ($itemFormatter) {
                $items = array_map($itemFormatter, $items);
            }
            return response()->json([
                'items' => $items,
                'nextCursor' => $result->nextCursor,
            ]);
        } else {
            $page = $request->integer('page', 1);
            $size = $request->integer('size', 10);
            $offsetRequest = new OffsetRequest(
                page: $page,
                size: $size,
                filters: $request->query('filter') ?? [],
            );
            $result = $dataFetcherByOffset($offsetRequest);
            if (!$result instanceof OffsetResponse) {
                return response()->json(['message' => 'Invalid response from offset data fetcher.'], 500);
            }
            $items = $result->items;
            if ($itemFormatter) {
                $items = array_map($itemFormatter, $items);
            }
            return response()->json([
                'items' => $items,
                'totalCount' => $result->totalCount,
                'page' => $page,
                'size' => $size,
                'hasMorePages' => $result->hasMorePages,
            ]);
        }
    }

    public static function buildCursorQuery(
        Builder  $query,
        string   $cursorName,
        ?string  $cursor,
        int      $size,
        callable $mapper
    ): CursorResponse
    {
        $laravelCursor = $cursor ? new Cursor([$cursorName => $cursor]) : null;
        $result = $query->cursorPaginate(perPage: $size, cursor: $laravelCursor);

        $data = $result->items();
        /** @var PaginableByCursor[] $items */
        $items = collect($data)
            ->map(fn($item) => $mapper($item))
            ->toArray();

        return new CursorResponse(
            nextCursor: $result->hasMorePages() ? end($items)?->getCursor() : null,
            items: $items
        );
    }
}
