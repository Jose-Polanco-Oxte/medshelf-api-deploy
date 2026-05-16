<?php

namespace App\Http\Controllers;

use App\Core\Home\Profile\Application\Dto\Request\AddProfileRequest;
use App\Core\Home\Profile\Application\Dto\Request\UpdateProfileRequest;
use App\Core\Home\Profile\Application\UseCase\AddProfile;
use App\Core\Home\Profile\Application\UseCase\UpdateProfile;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Home\Profile\Service\ProfileFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileFinder   $finder,
        protected AddProfile      $addProfile,
        protected UpdateProfile   $updateProfile,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/profiles",
     *     tags={"Profiles"},
     *     summary="Profiles list",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string", maxLength=255), description="Partial filter by name"),
     *     @OA\Response(
     *         response=200,
     *         description="Profiles list",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProfileResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProfileResponse")),
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
    public function index(UuidListRequest $request): JsonResponse
    {
        $userId = $this->getAuthUserId();

        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByUserIdByCursor($userId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByUserIdByOffset($userId, $offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/profiles",
     *     tags={"Profiles"},
     *     summary="Create profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","birthDate"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Maria"),
     *             @OA\Property(property="relationship", type="string", maxLength=255, example="parent"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="1995-08-20"),
     *             @OA\Property(property="allergies", type="array", @OA\Items(type="string"), example={"Penicillin","Pollen"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ProfileResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $this->getAuthUserId();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:255',
            'birthDate' => 'required|date_format:Y-m-d',
            'allergies' => 'nullable|array',
            'allergies.*' => 'string|max:255',
        ]);

        $allergies = array_values(array_unique(array_filter(array_map(
            fn(string $name) => trim($name),
            $data['allergies'] ?? []
        ))));

        $result = $this->addProfile->execute(new AddProfileRequest(
            userId: $userId,
            name: $data['name'],
            relationship: $data['relationship'] ?? null,
            birthDate: $data['birthDate'],
            allergies: $allergies,
        ));

        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'relationship' => $result->relationship,
            'birthDate' => $result->birthDate->toDateString(),
            'allergies' => $result->allergies,
            'createdAt' => $result->createdAt->toIso8601String(),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/profiles/{profileId}",
     *     tags={"Profiles"},
     *     summary="Get profile details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="profileId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ProfileResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $profileId): JsonResponse
    {
        $profile = $this->finder->findById($profileId);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }

    /**
     * @OA\Patch(
     *     path="/profiles/{profileId}",
     *     tags={"Profiles"},
     *     summary="Partially update a profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="profileId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="Maria"),
     *             @OA\Property(property="relationship", type="string", maxLength=255, example="parent")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ProfileResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(Request $request, string $profileId): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'relationship' => 'sometimes|nullable|string|max:255',
        ]);

        $result = $this->updateProfile->execute(new UpdateProfileRequest(
            profileId: $profileId,
            name: $data['name'] ?? null,
            relationship: array_key_exists('relationship', $data) ? $data['relationship'] : null,
        ));

        return response()->json([
            'id'           => $result->id,
            'name'         => $result->name,
            'relationship' => $result->relationship,
            'createdAt'    => $result->createdAt->toIso8601String(),
        ]);
    }
}
