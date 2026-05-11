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

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $houses = $user->houses()->get();
        return response()->json($houses);
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
