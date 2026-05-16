<?php

namespace App\Http\Controllers;

use App\Core\Home\Treatment\Application\Dto\Request\AddTreatmentRequest;
use App\Core\Home\Treatment\Application\Dto\Request\RegisterDoseRequest;
use App\Core\Home\Treatment\Application\Dto\Request\TreatmentActionRequest;
use App\Core\Home\Treatment\Application\Dto\Request\UpdateTreatmentRequest;
use App\Core\Home\Treatment\Application\UseCase\AddTreatment;
use App\Core\Home\Treatment\Application\UseCase\CancelTreatment;
use App\Core\Home\Treatment\Application\UseCase\CompleteTreatment;
use App\Core\Home\Treatment\Application\UseCase\PauseTreatment;
use App\Core\Home\Treatment\Application\UseCase\RegisterDose;
use App\Core\Home\Treatment\Application\UseCase\ResumeTreatment;
use App\Core\Home\Treatment\Application\UseCase\UpdateTreatment;
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
        protected AddTreatment      $addTreatment,
        protected UpdateTreatment   $updateTreatment,
        protected PauseTreatment    $pauseTreatment,
        protected ResumeTreatment   $resumeTreatment,
        protected CancelTreatment   $cancelTreatment,
        protected CompleteTreatment $completeTreatment,
        protected RegisterDose      $registerDose,
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/treatments",
     *     tags={"Treatments"},
     *     summary="List treatments",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="profile_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
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
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentResponse")),
     *                 @OA\Property(property="nextCursor", type="string", nullable=true)
     *             ),
     *             @OA\Schema(
     *                 type="object",
     *                 required={"items","totalCount","page","size","hasMorePages"},
     *                 @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentResponse")),
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
        $profileId = $request->query('profile_id');

        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->treatmentFinder->listByProfileIdByCursor($profileId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->treatmentFinder->listByProfileIdByOffset($profileId, $offsetRequest),
        );
    }

    /**
     * @OA\Post(
     *     path="/treatments",
     *     tags={"Treatments"},
     *     summary="Create treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"profileId","itemId","frequencyValue","frequencyUnit","doseQuantity","startDate"},
     *             @OA\Property(property="profileId", type="string", format="uuid"),
     *             @OA\Property(property="itemId", type="string", format="uuid"),
     *             @OA\Property(property="frequencyValue", type="integer", minimum=1, example=8),
     *             @OA\Property(property="frequencyUnit", type="string", enum={"hours","days","weeks"}),
     *             @OA\Property(property="doseQuantity", type="number", minimum=0.01, example=1.5),
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
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'profileId' => 'required|uuid',
            'itemId' => 'required|uuid',
            'frequencyValue' => 'required|integer|min:1',
            'frequencyUnit' => 'required|string|in:hours,days,weeks',
            'doseQuantity' => 'required|numeric|min:0.01',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d',
        ]);

        $result = $this->addTreatment->execute(new AddTreatmentRequest(
            profileId: $data['profileId'],
            itemId: $data['itemId'],
            frequencyValue: $data['frequencyValue'],
            frequencyUnit: $data['frequencyUnit'],
            doseQuantity: $data['doseQuantity'],
            startDate: $data['startDate'],
            endDate: $data['endDate'] ?? null,
        ));

        return response()->json($result, 201);
    }

    /**
     * @OA\Get(
     *     path="/treatments/{treatmentId}",
     *     tags={"Treatments"},
     *     summary="Get treatment details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TreatmentResponse")),
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
     * @OA\Put(
     *     path="/treatments/{treatmentId}",
     *     tags={"Treatments"},
     *     summary="Update treatment",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="frequencyValue", type="integer", minimum=1),
     *             @OA\Property(property="frequencyUnit", type="string", enum={"hours","days","weeks"}),
     *             @OA\Property(property="doseQuantity", type="number", minimum=0.01),
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
            'frequencyValue' => 'nullable|integer|min:1',
            'frequencyUnit' => 'nullable|string|in:hours,days,weeks',
            'doseQuantity' => 'nullable|numeric|min:0.01',
            'endDate' => 'nullable|date_format:Y-m-d',
        ]);
        $result = $this->updateTreatment->execute(new UpdateTreatmentRequest(
            treatmentId: $treatmentId,
            frequencyValue: $data['frequencyValue'] ?? null,
            frequencyUnit: $data['frequencyUnit'] ?? null,
            doseQuantity: $data['doseQuantity'] ?? null,
            endDate: $data['endDate'] ?? null,
        ));
        return response()->json($result);
    }

    /**
     * @OA\Patch(
     *     path="/treatments/{treatmentId}",
     *     tags={"Treatments"},
     *     summary="Modify treatment status",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="treatmentId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"active","paused","completed","cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TreatmentResponse")),
     *     @OA\Response(response=400, description="Bad request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function modify(Request $request, string $treatmentId): JsonResponse
    {
        $data = $request->validate([
            'status' => 'nullable|string|in:active,paused,completed,cancelled',
        ]);
        return match ($data['status']) {
            'paused' => $this->pause($treatmentId),
            'active' => $this->resume($treatmentId),
            'cancelled' => $this->cancel($treatmentId),
            'completed' => $this->complete($treatmentId),
            default => response()->json(['message' => 'Invalid status'], 400),
        };
    }

    private function pause(string $treatmentId): JsonResponse
    {
        $result = $this->pauseTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    private function resume(string $treatmentId): JsonResponse
    {
        $result = $this->resumeTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    private function cancel(string $treatmentId): JsonResponse
    {
        $result = $this->cancelTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    private function complete(string $treatmentId): JsonResponse
    {
        $result = $this->completeTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
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
        $result = $this->registerDose->execute(new RegisterDoseRequest(
            treatmentId: $treatmentId,
            amount: $data['amount'],
            houseId: $houseId,
        ));

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
            'id'             => $treatment->id,
            'status'         => $treatment->status,
            'frequencyValue' => $treatment->frequencyValue,
            'frequencyUnit'  => $treatment->frequencyUnit,
            'doseQuantity'   => $treatment->doseQuantity,
            'startDate'      => $treatment->startDate?->toDateString(),
            'endDate'        => $treatment->endDate?->toDateString(),
        ], JSON_THROW_ON_ERROR);

        $image = QrCode::format('png')->size(300)->generate($payload);

        return response($image, 200, ['Content-Type' => 'image/png']);
    }
}
