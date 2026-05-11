<?php

namespace App\Http\Controllers;

use App\Core\Home\House\Application\Dto\Request\AddPlaceRequest;
use App\Core\Home\House\Application\Dto\Request\RemovePlaceRequest;
use App\Core\Home\House\Application\Dto\Request\RemovePlacesRequest;
use App\Core\Home\House\Application\Dto\Request\UpdatePlaceRequest;
use App\Core\Home\House\Application\Dto\Response\PlaceResponse;
use App\Core\Home\House\Application\UseCase\AddPlace;
use App\Core\Home\House\Application\UseCase\RemovePlace;
use App\Core\Home\House\Application\UseCase\RemovePlaces;
use App\Core\Home\House\Application\UseCase\UpdatePlace;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Home\House\Detail\PlaceDetail;
use App\Providers\Core\Home\House\Service\PlaceFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function __construct(
        protected PlaceFinder  $finder,
        protected AddPlace     $addPlace,
        protected UpdatePlace  $updatePlace,
        protected RemovePlaces $removePlaces,
        protected RemovePlace  $removePlace
    )
    {
    }

    public function index(UuidListRequest $request, string $houseId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByHouseIdByCursor($houseId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByHouseIdByOffset($houseId, $offsetRequest),
        );
    }

    public function store(Request $request, string $houseId): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required',
        ]);
        $result = $this->addPlace->execute(
            new AddPlaceRequest(
                $houseId,
                $data['name']
            )
        );
        return $this->buildResponse($result);
    }

    private function buildResponse(PlaceResponse|PlaceDetail $result): JsonResponse
    {
        if ($result instanceof PlaceDetail) {
            return response()->json($result);
        } else {
            return response()->json([
                'id' => $result->id,
                'house' => [
                    'id' => $result->houseId,
                ],
                'name' => $result->name,
                'createdAt' => $result->createdAt,
            ]);
        }
    }

    public function show(string $placeId): JsonResponse
    {
        $place = $this->finder->findById($placeId);
        if (!$place) {
            return response()->json(['message' => 'Place not found'], 404);
        }
        return $this->buildResponse($place);
    }

    public function update(Request $request, string $placeId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $data = $request->validate([
            'name' => 'required',
        ]);
        $result = $this->updatePlace->execute(
            new UpdatePlaceRequest(
                $placeId,
                $data['name'],
                $houseId
            )
        );
        return $this->buildResponse($result);
    }

    public function destroy(Request $request, string $placeId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $this->removePlace->execute(
            new RemovePlaceRequest(
                $placeId,
                $houseId
            )
        );
        return response()->json(null, 204);
    }

    public function bulkDelete(Request $request, string $houseId): JsonResponse
    {
        $data = $request->validate([
            'placeIds' => 'required|array',
            'placeIds.*' => 'uuid',
        ]);
        $this->removePlaces->execute(
            new RemovePlacesRequest(
                $houseId,
                $data['placeIds']
            )
        );
        return response()->json(null, 204);
    }
}
