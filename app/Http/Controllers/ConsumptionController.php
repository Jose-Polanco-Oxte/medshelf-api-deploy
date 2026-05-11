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

class ConsumptionController extends Controller
{
    public function __construct(
        protected ConsumptionFinder $finder,
        protected ConsumeItem       $consumeItem
    )
    {
    }

    public function index(UuidListRequest $request, string $itemId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByItemIdByCursor($itemId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByItemIdByOffset($itemId, $offsetRequest),
        );
    }

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

    public function show(string $consumptionId): JsonResponse
    {
        $consumption = $this->finder->findById($consumptionId);
        if (!$consumption) {
            return response()->json(['message' => 'Consumption not found'], 404);
        }
        return $this->buildResponse($consumption);
    }
}
