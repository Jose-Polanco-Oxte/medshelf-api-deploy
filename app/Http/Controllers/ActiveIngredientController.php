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

class ActiveIngredientController extends Controller
{
    public function __construct(
        protected ActiveIngredientFinder $finder,
        protected AddActiveIngredient    $addActiveIngredient,
        protected RemoveActiveIngredient $removeActiveIngredient,
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

    public function destroy(string $activeIngredientId): JsonResponse
    {
        $this->removeActiveIngredient->execute((int)$activeIngredientId);
        return response()->json(null, 204);
    }
}

