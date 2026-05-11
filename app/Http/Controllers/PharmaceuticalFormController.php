<?php

namespace App\Http\Controllers;

use App\Core\Catalog\Product\Application\Dto\Request\AddPharmaceuticalFormRequest;
use App\Core\Catalog\Product\Application\Dto\Response\PharmaceuticalFormItemResponse;
use App\Core\Catalog\Product\Application\UseCase\AddPharmaceuticalForm;
use App\Core\Catalog\Product\Application\UseCase\RemovePharmaceuticalForm;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\IntegerListRequest;
use App\Providers\Core\Catalog\Product\Service\PharmaceuticalFormFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PharmaceuticalFormController extends Controller
{
    public function __construct(
        protected PharmaceuticalFormFinder $finder,
        protected AddPharmaceuticalForm    $addPharmaceuticalForm,
        protected RemovePharmaceuticalForm $removePharmaceuticalForm,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/pharmaceutical-forms",
     *     tags={"PharmaceuticalForms"},
     *     summary="List pharmaceutical forms",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Parameter(name="filter[name]", in="query", required=false, @OA\Schema(type="string"), description="Filter by pharmaceutical form name (partial match)"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PharmaceuticalFormResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PharmaceuticalFormResponse")),
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
     *     path="/pharmaceutical-forms",
     *     tags={"PharmaceuticalForms"},
     *     summary="Add pharmaceutical form",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","consumptionType"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Tableta"),
     *             @OA\Property(property="consumptionType", type="string", enum={"Discrete","Continuous","Applicable","discrete","continuous","applicable"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PharmaceuticalFormResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'consumptionType' => 'required|string|in:Discrete,Continuous,Applicable,discrete,continuous,applicable',
        ]);

        $result = $this->addPharmaceuticalForm->execute(
            new AddPharmaceuticalFormRequest(
                name: $data['name'],
                consumptionType: $data['consumptionType'],
            )
        );

        return $this->buildResponse($result);
    }

    private function buildResponse(PharmaceuticalFormItemResponse $result): JsonResponse
    {
        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'consumptionType' => $result->consumptionType,
            'createdAt' => $result->createdAt,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/pharmaceutical-forms/{pharmaceuticalFormId}",
     *     tags={"PharmaceuticalForms"},
     *     summary="Remove pharmaceutical form",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="pharmaceuticalFormId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(string $pharmaceuticalFormId): JsonResponse
    {
        $this->removePharmaceuticalForm->execute((int)$pharmaceuticalFormId);
        return response()->json(null, 204);
    }
}
