<?php

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// レガシーJS・クエリパラメータ互換ハンドラー
Route::any('/restaurants.php', function (Request $request, RestaurantController $controller) {
    $id = $request->query('id');
    $method = $request->method();

    if ($method === 'GET') {
        return $id ? $controller->show((int)$id) : $controller->index($request);
    } elseif ($method === 'POST') {
        return $id ? $controller->update($request, (int)$id) : $controller->store($request);
    } elseif ($method === 'DELETE') {
        return $controller->destroy((int)$id);
    }
    return response()->json(['error' => 'Method not allowed'], 405);
});

Route::any('/reviews.php', function (Request $request, ReviewController $controller) {
    $id = $request->query('id');
    $method = $request->method();

    if ($method === 'GET') {
        return $controller->index($request);
    } elseif ($method === 'POST') {
        return $id ? $controller->update($request, (int)$id) : $controller->store($request);
    } elseif ($method === 'DELETE') {
        return $controller->destroy((int)$id);
    }
    return response()->json(['error' => 'Method not allowed'], 405);
});

// モダン RESTful API
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::post('/restaurants', [RestaurantController::class, 'store']);
Route::post('/restaurants/{id}', [RestaurantController::class, 'update']);
Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
Route::delete('/restaurants/{id}', [RestaurantController::class, 'destroy']);

Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store']);
Route::post('/reviews/{id}', [ReviewController::class, 'update']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
