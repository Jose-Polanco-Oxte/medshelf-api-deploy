<?php

namespace App\Http\Controllers;

use App\Providers\Core\Home\House\Service\HouseFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HouseController extends Controller
{
    public function __construct(
        protected HouseFinder $finder
    )
    {
    }

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

    public function show(string $houseId): JsonResponse
    {
        $house = $this->finder->findById($houseId);
        if (!$house) {
            return response()->json(['message' => 'House not found'], 404);
        }
        return response()->json($house);
    }
}
