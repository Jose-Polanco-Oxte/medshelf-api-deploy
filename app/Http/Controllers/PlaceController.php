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
use OpenApi\Annotations as OA;

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

    /**
     * @OA\Get(
     *     path="/houses/{houseId}/places",
     *     tags={"Places"},
     *     summary="Get all places in a house",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="houseId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter by name (partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlaceResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlaceResponse")),
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
    public function index(UuidListRequest $request, string $houseId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByHouseIdByCursor($houseId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByHouseIdByOffset($houseId, $offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/houses/{houseId}/places",
     *     tags={"Places"},
     *     summary="Add new place to a house",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="houseId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Cocina")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PlaceResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/places/{placeId}",
     *     tags={"Places"},
     *     summary="Get place details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="placeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PlaceResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $placeId): JsonResponse
    {
        $place = $this->finder->findById($placeId);
        if (!$place) {
            return response()->json(['message' => 'Place not found'], 404);
        }
        return $this->buildResponse($place);
    }

    /**
     * @OA\Put(
     *     path="/places/{placeId}",
     *     tags={"Places"},
     *     summary="Update place",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="placeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Botiquin")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PlaceResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/places/{placeId}",
     *     tags={"Places"},
     *     summary="Remove place",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="placeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(string $placeId): JsonResponse
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

    /**
     * @OA\Post(
     *     path="/houses/{houseId}/places/bulk-delete",
     *     tags={"Places"},
     *     summary="Remove places in bulk",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="houseId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"placeIds"},
     *             @OA\Property(
     *                 property="placeIds",
     *                 type="array",
     *                 @OA\Items(type="string", format="uuid")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
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
