<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QualitasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/qualitas', [QualitasController::class, 'default'])->name('qualitas');
Route::get('/download', [QualitasController::class, 'download'])->name('download');
Route::get('/upload', [QualitasController::class, 'upload'])->name('upload');