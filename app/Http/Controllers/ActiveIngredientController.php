<?php

namespace App\Http\Controllers;

use App\Core\Catalog\Product\Application\Dto\Request\AddActiveIngredientRequest;
use App\Core\Catalog\Product\Application\Dto\Response\ActiveIngredientResponse;
use App\Core\Catalog\Product\Application\UseCase\AddActiveIngredient;
use App\Core\Catalog\Product\Application\UseCase\RemoveActiveIngredient;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\IntegerListRequest;
use App\Providers\Core\Catalog\Product\Service\ActiveIngredientFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ActiveIngredientController extends Controller
{
    public function __construct(
        protected ActiveIngredientFinder $finder,
        protected AddActiveIngredient    $addActiveIngredient,
        protected RemoveActiveIngredient $removeActiveIngredient,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/active-ingredients",
     *     tags={"ActiveIngredients"},
     *     summary="List active ingredients",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter by active ingredient name (partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ActiveIngredientResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ActiveIngredientResponse")),
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
    public function index(IntegerListRequest $request): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByCursor($cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByOffset($offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/active-ingredients",
     *     tags={"ActiveIngredients"},
     *     summary="Add active ingredient",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Ibuprofeno")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ActiveIngredientResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $result = $this->addActiveIngredient->execute(
            new AddActiveIngredientRequest($data['name'])
        );

        return $this->buildResponse($result);
    }

    private function buildResponse(ActiveIngredientResponse $result): JsonResponse
    {
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'createdAt' => $result->createdAt,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/active-ingredients/{activeIngredientId}",
     *     tags={"ActiveIngredients"},
     *     summary="Remove active ingredient",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="activeIngredientId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(string $activeIngredientId): JsonResponse
    {
        $this->removeActiveIngredient->execute((int)$activeIngredientId);
        return response()->json(null, 204);
    }
}
