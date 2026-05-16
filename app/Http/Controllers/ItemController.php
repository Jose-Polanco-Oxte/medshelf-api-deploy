<?php

namespace App\Http\Controllers;

use App\Core\Home\Item\Application\Dto\Request\AddItemRequest;
use App\Core\Home\Item\Application\Dto\Request\RemoveItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ItemResponse;
use App\Core\Home\Item\Application\UseCase\AddItem;
use App\Core\Home\Item\Application\UseCase\RemoveItem;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Home\Item\Detail\ItemDetail;
use App\Providers\Core\Home\Item\Service\ItemFinder;
use App\Services\PaginationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ItemController extends Controller
{
    public function __construct(
        protected ItemFinder $finder,
        protected AddItem    $addItem,
        protected RemoveItem $removeItem
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/places/{placeId}/items",
     *     tags={"Items"},
     *     summary="Get list of items by place",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="placeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter items by product name (case-insensitive, partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemView")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemView")),
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
    public function index(UuidListRequest $request, string $placeId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByPlaceIdByCursor($placeId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByPlaceIdByOffset($placeId, $offsetRequest),
        );
    }

    /**
     * @OA\Get(
     *     path="/items",
     *     tags={"Items"},
     *     summary="Get list of items by house",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter items by product name (case-insensitive, partial match)"),
     *     @OA\Parameter(name="filter[productId]", in="query", required=false, @OA\Schema(type="string", format="uuid"), description="Filter items belonging to a specific product"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemView")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemView")),
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
    public function indexAll(UuidListRequest $request): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByHouseIdByCursor($houseId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByHouseIdByOffset($houseId, $offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/places/{placeId}/items",
     *     tags={"Items"},
     *     summary="Add item to place",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="placeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"productId","expirationDate"},
     *             @OA\Property(property="productId", type="string", format="uuid"),
     *             @OA\Property(property="expirationDate", type="string", format="date-time", example="2026-12-31T00:00:00Z")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ItemResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(Request $request, string $placeId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $data = $request->validate([
            'productId' => 'required|uuid',
            'expirationDate' => 'required|date|after:now',
        ]);
        $result = $this->addItem->execute(
            new AddItemRequest(
                $data['productId'],
                $placeId,
                Carbon::parse($data['expirationDate']),
                $houseId
            )
        );
        return $this->buildResponse($result);
    }

    private function buildResponse(ItemDetail|ItemResponse $result): JsonResponse
    {
        if ($result instanceof ItemDetail) {
            return response()->json($result);
        } else {
            return response()->json([
                'id' => $result->id,
                'product' => [
                    'id' => $result->productId,
                ],
                'place' => [
                    'id' => $result->placeId,
                ],
                'totalContent' => $result->totalContent,
                'expirationDate' => $result->expirationDate->toIso8601ZuluString('millisecond'),
                'createdAt' => $result->createdAt->toIso8601ZuluString('millisecond'),
            ], 201);
        }
    }

    /**
     * @OA\Get(
     *     path="/items/{itemId}",
     *     tags={"Items"},
     *     summary="Get item details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="itemId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ItemDetail")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $itemId): JsonResponse
    {
        $item = $this->finder->findById($itemId);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return $this->buildResponse($item);
    }

    /**
     * @OA\Delete(
     *     path="/items/{itemId}",
     *     tags={"Items"},
     *     summary="Remove item",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="itemId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(string $itemId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $this->removeItem->execute(
            new RemoveItemRequest(
                $itemId,
                $houseId
            )
        );
        return response()->json(null, 204);
    }
}
