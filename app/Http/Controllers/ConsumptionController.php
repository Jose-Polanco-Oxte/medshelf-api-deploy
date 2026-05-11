<?php

namespace App\Http\Controllers;

use App\Core\Home\Item\Application\Dto\Request\ConsumeItemRequest;
use App\Core\Home\Item\Application\Dto\Response\ConsumptionResponse;
use App\Core\Home\Item\Application\UseCase\ConsumeItem;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Home\Item\Detail\ConsumptionDetail;
use App\Providers\Core\Home\Item\Service\ConsumptionFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ConsumptionController extends Controller
{
    public function __construct(
        protected ConsumptionFinder $finder,
        protected ConsumeItem       $consumeItem
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/items/{itemId}/consumptions",
     *     tags={"Consumptions"},
     *     summary="List item consumptions",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="itemId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter by consumer name (partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
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
    public function index(UuidListRequest $request, string $itemId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByItemIdByCursor($itemId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByItemIdByOffset($itemId, $offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/items/{itemId}/consumptions",
     *     tags={"Consumptions"},
     *     summary="Register consumption",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="itemId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", minimum=0, example=1.5)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ConsumptionResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(string $itemId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $data = request()->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->consumeItem->execute(
            new ConsumeItemRequest(
                $itemId,
                $data['amount'],
                $houseId
            )
        );
        return $this->buildResponse($result);
    }

    private function buildResponse(ConsumptionResponse|ConsumptionDetail $result): JsonResponse
    {
        if ($result instanceof ConsumptionDetail) {
            return response()->json($result);
        } else {
            return response()->json([
                'id' => $result->id,
                'item' => [
                    'id' => $result->itemId,
                ],
                'amount' => $result->amount,
                'consumedAt' => $result->consumedAt,
            ], 201);
        }
    }

    /**
     * @OA\Get(
     *     path="/consumptions/{consumptionId}",
     *     tags={"Consumptions"},
     *     summary="Get consumption details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="consumptionId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ConsumptionResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $consumptionId): JsonResponse
    {
        $consumption = $this->finder->findById($consumptionId);
        if (!$consumption) {
            return response()->json(['message' => 'Consumption not found'], 404);
        }
        return $this->buildResponse($consumption);
    }
}
