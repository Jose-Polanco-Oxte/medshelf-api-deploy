<?php

namespace App\Http\Controllers;

use App\Core\Catalog\Product\Application\Dto\Request\ActiveIngredientRequest;
use App\Core\Catalog\Product\Application\Dto\Request\AddProductRequest;
use App\Core\Catalog\Product\Application\Dto\Request\CompositionRequest;
use App\Core\Catalog\Product\Application\Dto\Request\NetContentRequest;
use App\Core\Catalog\Product\Application\Dto\Request\StrengthRequest;
use App\Core\Catalog\Product\Application\Dto\Response\ProductResponse;
use App\Core\Catalog\Product\Application\UseCase\AddProduct;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Catalog\Product\Detail\ProductDetail;
use App\Providers\Core\Catalog\Product\Service\ProductFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductFinder $finder,
        protected AddProduct    $addProduct,
    )
    {
    }

    public function index(UuidListRequest $request): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByCursor($cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByOffset($offsetRequest),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',

            'netContent' => 'nullable|array',
            'netContent.value' => 'required_with:netContent|numeric|min:0',
            'netContent.unit' => 'required_with:netContent|string',

            'totalQuantity' => 'nullable|numeric|min:0',

            'pharmaceuticalForm' => 'required|string|max:255',

            'composition' => 'required|array',
            'composition.referenceAmount' => 'required|numeric|min:0',
            'composition.activeIngredients' => 'required|array|min:0',
            'composition.activeIngredients.*.name' => 'required|string|max:255',
            'composition.activeIngredients.*.strength' => 'required|array',
            'composition.activeIngredients.*.strength.value' => 'required|numeric|min:0',
            'composition.activeIngredients.*.strength.unit' => 'required|string',
        ]);
        $result = $this->addProduct->execute(
            new AddProductRequest(
                name: $data['name'],
                netContent: isset($data['netContent']) ? new NetContentRequest(
                    value: $data['netContent']['value'],
                    unit: $data['netContent']['unit']
                ) : null,
                totalQuantity: $data['totalQuantity'] ?? null,
                pharmaceuticalForm: $data['pharmaceuticalForm'],
                composition: new CompositionRequest(
                    $data['composition']['referenceAmount'],
                    array_map(fn(array $ingredient) => new ActiveIngredientRequest(
                        name: $ingredient['name'],
                        strength: new StrengthRequest(
                            value: $ingredient['strength']['value'],
                            unit: $ingredient['strength']['unit']
                        )
                    ), $data['composition']['activeIngredients'])
                )
            )
        );
        return $this->buildResponse($result);
    }

    private function buildResponse(ProductDetail|ProductResponse $result): JsonResponse
    {
        if ($result instanceof ProductDetail) {
            return response()->json($result);
        } else {
            return response()->json([
                'id' => $result->id,
                'name' => $result->name,
                'netContent' => $result->netContent ?? null,
                'totalQuantity' => $result->totalQuantity ?? null,
                'pharmaceuticalForm' => [
                    'name' => $result->pharmaceuticalForm->name,
                    'consumptionType' => $result->pharmaceuticalForm->consumptionType,
                ],
                'createdAt' => $result->createdAt,
                'composition' => $result->composition,
            ]);
        }
    }

    public function show(string $productId): JsonResponse
    {
        $product = $this->finder->findById($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return $this->buildResponse($product);
    }
}
