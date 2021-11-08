<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiQualitasController;

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
Route::prefix('qualitas')->group(function()
{
	Route::get('/', [ApiQualitasController::class, 'list']);
	Route::post('/', [ApiQualitasController::class, 'list']);
	Route::post('/download', [ApiQualitasController::class, 'download']);
	Route::post('/upload-ftp', [ApiQualitasController::class, 'uploadFtp']);
});