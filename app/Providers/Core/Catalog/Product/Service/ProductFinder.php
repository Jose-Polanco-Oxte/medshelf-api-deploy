<?php

namespace App\Providers\Core\Catalog\Product\Service;

use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\CursorResponse;
use App\Core\Shared\Domain\OffsetRequest;
use App\Core\Shared\Domain\OffsetResponse;
use App\Models\ProductCompoundModel;
use App\Models\ProductModel;
use App\Providers\Core\Catalog\Product\Detail\ProductDetail;
use App\Providers\Core\Catalog\Product\View\ProductView;
use App\Providers\Core\Catalog\Product\Wrapper\ActiveIngredient;
use App\Providers\Core\Catalog\Product\Wrapper\Composition;
use App\Providers\Core\Catalog\Product\Wrapper\NetContent;
use App\Providers\Core\Catalog\Product\Wrapper\PharmaceuticalForm;
use App\Providers\Core\Catalog\Product\Wrapper\Strength;
use App\Providers\Core\InvalidCursor;
use App\Services\PaginationService;

class ProductFinder
{

    function findById(string $id): ?ProductDetail
    {
        $record = ProductModel::with([
            'activeCompounds.activeIngredient',
            'pharmaceuticalForm',
        ])
            ->where('public_id', $id)
            ->first();

        if (!$record) return null;
        return $this->toDetail($record);
    }

    private function toDetail(ProductModel $record): ProductDetail
    {
        return new ProductDetail(
            id: $record->public_id,
            name: $record->name,
            netContent: ($record->net_content_value && $record->net_content_unit)
                ? new NetContent(value: $record->net_content_value, unit: $record->net_content_unit,)
                : null,
            totalQuantity: $record->total_quantity,
            pharmaceuticalForm: new PharmaceuticalForm(
                name: $record->pharmaceuticalForm->name,
                consumptionType: $record->pharmaceuticalForm->consumption_type,
            ),
            createdAt: $record->created_at->toIso8601ZuluString('millisecond'),
            composition: new Composition(
                referenceAmount: $record->composition_reference_amount,
                activeIngredients: $record->activeCompounds
                    ->map(
                        fn(ProductCompoundModel $compound) => new ActiveIngredient(
                            name: $compound->activeIngredient->name,
                            strength: new Strength(
                                value: $compound->strength_value,
                                unit: $compound->strength_unit,
                            )
                        )
                    )->toArray()
            )
        );
    }

    function listByOffset(OffsetRequest $request): OffsetResponse
    {
        $result = ProductModel::with(['pharmaceuticalForm'])
            ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"))
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

    private function toView(ProductModel $record): ProductView
    {
        return new ProductView(
            id: $record->public_id,
            name: $record->name,
            netContent: ($record->net_content_value && $record->net_content_unit)
                ? new NetContent(value: $record->net_content_value, unit: $record->net_content_unit,)
                : null,
            totalQuantity: $record->total_quantity,
            pharmaceuticalForm: new PharmaceuticalForm(
                name: $record->pharmaceuticalForm->name,
                consumptionType: $record->pharmaceuticalForm->consumption_type,
            )
        );
    }

    function listByCursor(CursorRequest $request): CursorResponse
    {
        $id = match ($request->cursor) {
            null => null,
            default => ProductModel::where('public_id', $request->cursor)->value('id')
                ?? throw new InvalidCursor('Invalid cursor provided for Product listing.')
        };

        return PaginationService::buildCursorQuery(
            query: ProductModel::with(['pharmaceuticalForm'])
                ->when($request->filters['name'] ?? null, fn($q, $v) => $q->whereLike('name', "%$v%"))
                ->orderBy('id'),
            cursorName: 'id',
            cursor: $id,
            size: $request->size,
            mapper: fn($item) => $this->toView($item)
        );
    }
}
