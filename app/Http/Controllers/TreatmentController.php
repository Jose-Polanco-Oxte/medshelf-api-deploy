<?php

namespace App\Http\Controllers;

use App\Core\Home\Profile\Application\Exception\ProfileNotFound;
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

    public function index(UuidListRequest $request): JsonResponse
    {
        $profileId = $request->query('profile_id');

        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->treatmentFinder->listByProfileIdByCursor($profileId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->treatmentFinder->listByProfileIdByOffset($profileId, $offsetRequest),
        );
    }

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

        try {
            $result = $this->addTreatment->execute(new AddTreatmentRequest(
                profileId: $data['profileId'],
                itemId: $data['itemId'],
                frequencyValue: $data['frequencyValue'],
                frequencyUnit: $data['frequencyUnit'],
                doseQuantity: $data['doseQuantity'],
                startDate: $data['startDate'],
                endDate: $data['endDate'] ?? null,
            ));
        } catch (ProfileNotFound $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json($result, 201);
    }

    public function show(string $treatmentId): JsonResponse
    {
        $treatment = $this->treatmentFinder->findById($treatmentId);

        if (!$treatment) {
            return response()->json(['message' => 'Treatment not found'], 404);
        }

        return response()->json($treatment);
    }

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

    public function pause(string $treatmentId): JsonResponse
    {
        $result = $this->pauseTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    public function resume(string $treatmentId): JsonResponse
    {
        $result = $this->resumeTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    public function cancel(string $treatmentId): JsonResponse
    {
        $result = $this->cancelTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

    public function complete(string $treatmentId): JsonResponse
    {
        $result = $this->completeTreatment->execute(new TreatmentActionRequest($treatmentId));
        return response()->json($result);
    }

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

    public function indexDoses(UuidListRequest $request, string $treatmentId): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->consumptionFinder->listByTreatmentIdByCursor($treatmentId, $cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->consumptionFinder->listByTreatmentIdByOffset($treatmentId, $offsetRequest),
        );
    }
}
