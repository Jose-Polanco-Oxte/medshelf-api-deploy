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

class PharmaceuticalFormController extends Controller
{
    public function __construct(
        protected PharmaceuticalFormFinder $finder,
        protected AddPharmaceuticalForm    $addPharmaceuticalForm,
        protected RemovePharmaceuticalForm $removePharmaceuticalForm,
    )
    {
    }

    public function index(IntegerListRequest $request): JsonResponse
    {
        return PaginationService::paginate(
            $request,
            fn(CursorRequest $cursorRequest) => $this->finder->listByCursor($cursorRequest),
            fn(OffsetRequest $offsetRequest) => $this->finder->listByOffset($offsetRequest),
        );
    }

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

    public function destroy(string $pharmaceuticalFormId): JsonResponse
    {
        $this->removePharmaceuticalForm->execute((int)$pharmaceuticalFormId);
        return response()->json(null, 204);
    }
}
