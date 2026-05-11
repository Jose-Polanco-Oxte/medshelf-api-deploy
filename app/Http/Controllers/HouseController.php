<?php

namespace App\Http\Controllers;

use App\Providers\Core\Home\House\Service\HouseFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class HouseController extends Controller
{
    public function __construct(
        protected HouseFinder $finder
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/houses/me",
     *     tags={"Houses"},
     *     summary="Get my house details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/HouseResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function myHouse(): JsonResponse
    {
        $user = Auth::user();
        $houseId = $this->getAuthHouseId();
        $houses = $user->house;
        return response()->json([
            'id' => $houses->public_id,
            'owner' => [
                'id' => $user->public_id,
                'name' => $user->name,
            ],
            'name' => $houses->name,
            'createdAt' => $houses->created_at,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/houses/{houseId}",
     *     tags={"Houses"},
     *     summary="Get house details by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="houseId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/HouseResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $houseId): JsonResponse
    {
        $house = $this->finder->findById($houseId);
        if (!$house) {
            return response()->json(['message' => 'House not found'], 404);
        }
        return response()->json($house);
    }
}
