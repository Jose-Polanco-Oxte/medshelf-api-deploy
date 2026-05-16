<?php

namespace App\Http\Controllers;

use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
use App\Core\Home\Treatment\Application\Dto\Request\CreateTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Request\RegisterDoseRequest;
use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\Exception\TreatmentNotFound;
use App\Core\Home\Treatment\Application\UseCase\CreateTreatment;
use App\Core\Home\Treatment\Application\UseCase\RegisterDose;
use App\Core\Home\Treatment\Application\UseCase\UpdateTreatment;
use App\Core\Home\Treatment\Model\Exception\TreatmentException;
use App\Core\Shared\Domain\CursorRequest;
use App\Core\Shared\Domain\OffsetRequest;
use App\Http\Requests\UuidListRequest;
use App\Providers\Core\Home\Item\Service\ConsumptionFinder;
use App\Providers\Core\Home\Treatment\Service\TreatmentFinder;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TreatmentController extends Controller
{
    public function __construct(
        protected TreatmentFinder   $treatmentFinder,
        protected ConsumptionFinder $consumptionFinder,
        protected CreateTreatment   $addTreatment,
        protected UpdateTreatment   $updateTreatment,
        protected RegisterDose      $registerDose,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/profiles/{profileId}/treatments",
     *     tags={"Treatments"},
     *     summary="List treatments for profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="profileId", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentView")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentView")),
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
    public function index(UuidListRequest $request, string $profileId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->treatmentFinder->listByProfileIdByCursor($profileId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->treatmentFinder->listByProfileIdByOffset($profileId, $offsetRequest),
        );
    }

    /**
     * @OA\Get(
     *     path="/treatments",
     *     tags={"Treatments"},
     *     summary="List treatments for authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentView")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentView")),
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
    public function indexAll(UuidListRequest $request): JsonResponse
    {
        $userId = $this->getAuthUserId();
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $request) => $this->treatmentFinder->listByUserIdByCursor($userId, $request),
            fn(OffsetRequest $request) => $this->treatmentFinder->listByUserIdByOffset($userId, $request),
        );
    }

    /**
     * @OA\Post(
     *     path="/profiles/{profileId}/treatments",
     *     parameters={
     *         @OA\Parameter(name="profileId", in="query", required=true, @OA\Schema(type="string", format="uuid"))
     *     },
     *     tags={"Treatments"},
     *     summary="Create treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"itemId","dose","frequencyUnit","startDate"},
     *             @OA\Property(property="itemId", type="string", format="uuid"),
     *             @OA\Property(property="dose", type="number", minimum=0.01, example=1.5),
     *             @OA\Property(property="frequencyUnit", type="string", enum={"hours","days","weeks"}),
     *             @OA\Property(property="startDate", type="string", format="date", example="2026-05-11"),
     *             @OA\Property(property="endDate", type="string", format="date", example="2026-05-30")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/TreatmentResponse")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(Request $request, string $profileId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();
        $data = $request->validate([
            'itemId' => 'required|uuid',
            'dose' => 'required|numeric:min:0.01',
            'frequencyUnit' => 'required|string|in:hours,days,weeks',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            $result = $this->addTreatment->execute(new CreateTreatmentRequest(
                profileId: $profileId,
                itemId: $data['itemId'],
                houseId: $houseId,
                dose: $data['dose'],
                frequencyUnit: $data['frequencyUnit'],
                startDate: $data['startDate'],
                endDate: $data['endDate'] ?? null,
            ));
        } catch (ProfileNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'id' => $result->id,
            'profile' => [
                'id' => $result->profileId,
            ],
            'item' => [
                'id' => $result->itemId,
            ],
            'status' => $result->status,
            'dose' => $result->dose,
            'frequencyUnit' => $result->frequencyUnit,
            'startDate' => $result->startDate,
            'endDate' => $result->endDate,
            'createdAt' => $result->createdAt,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/treatments/{treatmentId}",
     *     tags={"Treatments"},
     *     summary="Get treatment details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TreatmentView")),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $treatmentId): JsonResponse
    {
        $treatment = $this->treatmentFinder->findById($treatmentId);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment not found'], 404);
        }

        return response()->json($treatment);
    }

    /**
     * @OA\Patch(
     *     path="/treatments/{treatmentId}",
     *     tags={"Treatments"},
     *     summary="Update treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="dose", type="number", minimum=0.01, example=1.5),
     *             @OA\Property(property="frequencyUnit", type="string", enum={"hours","days","weeks"}),
     *             @OA\Property(property="status", type="string", enum={"active","paused","completed","cancelled"}),
     *             @OA\Property(property="endDate", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TreatmentResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(Request $request, string $treatmentId): JsonResponse
    {
        $data = $request->validate([
            'dose' => 'nullable|numeric|min:0.01',
            'frequencyUnit' => 'nullable|string|in:hours,days,weeks',
            'status' => 'nullable|string|in:active,paused,completed,cancelled',
            'endDate' => 'nullable|date_format:Y-m-d',
        ]);
        try {
            $result = $this->updateTreatment->execute(new UpdateTreatmentRequest(
                treatmentId: $treatmentId,
                dose: $data['dose'] ?? null,
                frequencyUnit: $data['frequencyUnit'] ?? null,
                status: $data['status'] ?? null,
                endDate: $data['endDate'] ?? null,
            ));
        } catch (TreatmentNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'id' => $result->id,
            'profile' => [
                'id' => $result->profileId,
            ],
            'item' => [
                'id' => $result->itemId,
            ],
            'status' => $result->status,
            'dose' => $result->dose,
            'frequencyUnit' => $result->frequencyUnit,
            'startDate' => $result->startDate,
            'endDate' => $result->endDate,
            'createdAt' => $result->createdAt,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/treatments/{treatmentId}/consumptions",
     *     tags={"Treatments"},
     *     summary="Register dose for treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", minimum=0.01, example=1.0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ConsumptionResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function storeDose(Request $request, string $treatmentId): JsonResponse
    {
        $houseId = $this->getAuthHouseId();

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);
        try {
            $result = $this->registerDose->execute(new RegisterDoseRequest(
                treatmentId: $treatmentId,
                amount: $data['amount'],
                houseId: $houseId,
            ));
        } catch (TreatmentNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (TreatmentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/treatments/{treatmentId}/consumptions",
     *     tags={"Treatments"},
     *     summary="List doses for treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *     @OA\Parameter(name="cursor", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="size", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(oneOf={
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","nextCursor"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
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
    public function indexDoses(UuidListRequest $request, string $treatmentId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->consumptionFinder->listByTreatmentIdByCursor($treatmentId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->consumptionFinder->listByTreatmentIdByOffset($treatmentId, $offsetRequest),
        );
    }

    /**
     * @OA\Get(
     *     path="/treatments/{treatmentId}/qr",
     *     tags={"Treatments"},
     *     summary="Generate QR code image for a treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="PNG image of the QR code encoding the treatment summary",
     *         @OA\MediaType(mediaType="image/png", @OA\Schema(type="string", format="binary"))
     *     ),
     *     @OA\Response(response=404, description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function qr(string $treatmentId): Response|JsonResponse
    {
        $treatment = $this->treatmentFinder->findById($treatmentId);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment not found'], 404);
        }

        $payload = json_encode([
            'id' => $treatment->id,
            'status' => $treatment->status,
            'dose' => $treatment->dose,
            'frequencyUnit' => $treatment->frequencyUnit,
            'startDate' => $treatment->startDate->toDateString(),
            'endDate' => $treatment->endDate?->toDateString(),
        ], JSON_THROW_ON_ERROR);

        $image = QrCode::format('png')->size(300)->generate($payload);

        return response($image, 200, ['Content-Type' => 'image/png']);
    }
}
