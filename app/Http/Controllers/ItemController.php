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

class ItemController extends Controller
{
    public function __construct(
        protected ItemFinder $finder,
        protected AddItem    $addItem,
        protected RemoveItem $removeItem
    )
    {
    }

    public function index(UuidListRequest $request, string $placeId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByPlaceIdByCursor($placeId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByPlaceIdByOffset($placeId, $offsetRequest),
        );
    }

    public function store(Request $request, string $placeId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $data = $request->validate([
            'productId' => 'required|uuid',
            'expirationDate' => 'required|date_format:Y-m-d\TH:i:s.v\Z,Y-m-d\TH:i:s\Z',
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
                'expirationDate' => $result->expirationDate->toDateString(),
                'createdAt' => $result->createdAt->toDateString(),
            ], 201);
        }
    }

    public function show(string $itemId): JsonResponse
    {
        $item = $this->finder->findById($itemId);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return $this->buildResponse($item);
    }

    public function destroy(Request $request, string $itemId): JsonResponse
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
