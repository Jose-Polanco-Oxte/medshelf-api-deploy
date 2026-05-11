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
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    public function __construct(
        protected ProductFinder $finder,
        protected AddProduct    $addProduct,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/products",
     *     tags={"Products"},
     *     summary="List products",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter by product name (partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProductResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProductResponse")),
     *                 @OA\Property(property="totalCount", type="integer", minimum=0),
     *                 @OA\Property(property="page", type="integer", minimum=1),
     *                 @OA\Property(property="size", type="integer", minimum=1, maximum=100),
     *                 @OA\Property(property="hasMorePages", type="boolean")
     *             )
     *         })
     *     ),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(UuidListRequest $request): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByCursor($cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByOffset($offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     tags={"Products"},
     *     summary="Add product",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","pharmaceuticalForm","composition"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Paracetamol"),
     *             @OA\Property(
     *                 property="netContent",
     *                 type="object",
     *                 @OA\Property(property="value", type="number", minimum=0, example=100),
     *                 @OA\Property(property="unit", type="string", example="ml")
     *             ),
     *             @OA\Property(property="totalQuantity", type="number", minimum=0, example=20),
     *             @OA\Property(property="pharmaceuticalForm", type="string", example="Jarabe"),
     *             @OA\Property(
     *                 property="composition",
     *                 type="object",
     *                 @OA\Property(property="referenceAmount", type="number", minimum=0, example=5),
     *                 @OA\Property(
     *                     property="activeIngredients",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Acetaminofen"),
     *                         @OA\Property(
     *                             property="strength",
     *                             type="object",
     *                             @OA\Property(property="value", type="number", minimum=0, example=500),
     *                             @OA\Property(property="unit", type="string", example="mg")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/products/{productId}",
     *     tags={"Products"},
     *     summary="Get product details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ProductResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $productId): JsonResponse
    {
        $product = $this->finder->findById($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return $this->buildResponse($product);
    }
}
