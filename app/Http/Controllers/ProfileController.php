<?php

namespace App\Http\Controllers;

use App\Core\Home\Profile\Application\Dto\Request\AddProfileRequest;
use App\Core\Home\Profile\Application\UseCase\AddProfile;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\ListRequest;
use App\Providers\Core\Home\Profile\Service\ProfileFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileFinder $finder,
        protected AddProfile    $addProfile,
    )
    {
    }

    public function index(ListRequest $request): JsonResponse
    {
        $userId = $request->header('X-User-Id');

        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByUserIdByCursor($userId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByUserIdByOffset($userId, $offsetRequest),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $userId = $request->header('X-User-Id');

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'relationship' => 'nullable|string|max:255',
        ]);

        $result = $this->addProfile->execute(new AddProfileRequest(
            userId: $userId,
            name: $data['name'],
            relationship: $data['relationship'] ?? null,
        ));

        return response()->json([
            'id'           => $result->id,
            'userId'       => $result->userId,
            'name'         => $result->name,
            'relationship' => $result->relationship,
            'createdAt'    => $result->createdAt->toIso8601String(),
        ], 201);
    }

    public function show(string $profileId): JsonResponse
    {
        $profile = $this->finder->findById($profileId);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }
}
