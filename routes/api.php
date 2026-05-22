<?php

use App\Http\Controllers\QzApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
| ─── Puente Quezada → SOMA cloud ───
| Endpoints consumidos por SOMA cloud (que no tiene ruta directa al MySQL
| de QZ). Protegidos con header X-API-Key contra SOMA_INBOUND_API_KEY.
*/
Route::middleware('soma.api')->prefix('qz')->group(function () {
    Route::get('/disponible',      [QzApiController::class, 'disponible']);
    Route::get('/verificados-hoy', [QzApiController::class, 'verificadosHoy']);
    Route::get('/folio/{folio}',   [QzApiController::class, 'obtenerPorFolio'])
        ->where('folio', '[A-Za-z0-9_-]+');
});
