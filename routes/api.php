<?php

use App\Http\Controllers\ActiveIngredientController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PharmaceuticalFormController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('/houses/{houseId}', [HouseController::class, 'show']);

    // Places
    Route::get('/houses/{houseId}/places', [PlaceController::class, 'index']);
    Route::get('/places/{placeId}', [PlaceController::class, 'show']);
    Route::post('/houses/{houseId}/places', [PlaceController::class, 'store']);
    Route::post('/houses/{houseId}/places/bulk-delete', [PlaceController::class, 'bulkDelete']);
    Route::put('/places/{placeId}', [PlaceController::class, 'update']);
    Route::delete('/places/{placeId}', [PlaceController::class, 'destroy']);

    // Items
    Route::get('/places/{placeId}/items', [ItemController::class, 'index']);
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

    // Active Ingredients
    Route::get('/active-ingredients', [ActiveIngredientController::class, 'index']);
    Route::post('/active-ingredients', [ActiveIngredientController::class, 'store']);
    Route::delete('/active-ingredients/{activeIngredientId}', [ActiveIngredientController::class, 'destroy']);

    // Pharmaceutical Forms
    Route::get('/pharmaceutical-forms', [PharmaceuticalFormController::class, 'index']);
    Route::post('/pharmaceutical-forms', [PharmaceuticalFormController::class, 'store']);
    Route::delete('/pharmaceutical-forms/{pharmaceuticalFormId}', [PharmaceuticalFormController::class, 'destroy']);
});