<?php

use App\Http\Controllers\ActiveIngredientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PharmaceuticalFormController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreatmentController;
use App\Http\Middleware\AuthenticateApi;
use App\Http\Middleware\JwtCookieMiddleware;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Auth routes (protected)
Route::prefix('auth')->middleware([JwtCookieMiddleware::class, AuthenticateApi::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/account', [AuthController::class, 'account']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);
    Route::patch('/account', [AuthController::class, 'updateAccount']);
});

Route::group(['middleware' => [JwtCookieMiddleware::class, AuthenticateApi::class]], function () {
    Route::get('/houses/me', [HouseController::class, 'show']);

    // Places
    Route::get('/houses/{houseId}/places', [PlaceController::class, 'index']);
    Route::get('/places/{placeId}', [PlaceController::class, 'show']);
    Route::post('/houses/{houseId}/places', [PlaceController::class, 'store']);
    Route::post('/houses/{houseId}/places/bulk-delete', [PlaceController::class, 'bulkDelete']);
    Route::put('/places/{placeId}', [PlaceController::class, 'update']);
    Route::delete('/places/{placeId}', [PlaceController::class, 'destroy']);

    // Items
    Route::get('/places/{placeId}/items', [ItemController::class, 'index']);
    Route::get('/items', [ItemController::class, 'indexAll']);
    Route::get('/items/{itemId}', [ItemController::class, 'show']);
    Route::post('/places/{placeId}/items', [ItemController::class, 'store']);
    Route::delete('/items/{itemId}', [ItemController::class, 'destroy']);

    // Consumptions
    Route::get('/items/{itemId}/consumptions', [ConsumptionController::class, 'index']);
    Route::get('/consumptions/{consumptionId}', [ConsumptionController::class, 'show']);
    Route::post('/items/{itemId}/consumptions', [ConsumptionController::class, 'store']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{productId}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);

    // Profiles
    Route::get('/profiles', [ProfileController::class, 'index']);
    Route::get('/profiles/{profileId}', [ProfileController::class, 'show']);
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::patch('/profiles/{profileId}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profileId}', [ProfileController::class, 'destroy']);

    // Treatments
    Route::post('/profiles/{profileId}/treatments', [TreatmentController::class, 'store']);
    Route::get('/profiles/{profileId}/treatments', [TreatmentController::class, 'index']);
    Route::get('/treatments/{treatmentId}', [TreatmentController::class, 'show']);
    Route::get('/treatments', [TreatmentController::class, 'indexAll']);
    Route::patch('/treatments/{treatmentId}', [TreatmentController::class, 'update']);
    Route::post('/treatments/{treatmentId}/consumptions', [TreatmentController::class, 'storeDose']);
    Route::get('/treatments/{treatmentId}/consumptions', [TreatmentController::class, 'indexDoses']);
    Route::get('/treatments/{treatmentId}/qr', [TreatmentController::class, 'qr']);

    // Active Ingredients
    Route::get('/active-ingredients', [ActiveIngredientController::class, 'index']);
    Route::post('/active-ingredients', [ActiveIngredientController::class, 'store']);
    Route::delete('/active-ingredients/{activeIngredientId}', [ActiveIngredientController::class, 'destroy']);

    // Pharmaceutical Forms
    Route::get('/pharmaceutical-forms', [PharmaceuticalFormController::class, 'index']);
    Route::post('/pharmaceutical-forms', [PharmaceuticalFormController::class, 'store']);
    Route::delete('/pharmaceutical-forms/{pharmaceuticalFormId}', [PharmaceuticalFormController::class, 'destroy']);
});
