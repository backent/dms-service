<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AWSController;
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

Route::post('/upload', [AWSController::class, 'upload']);
Route::post('/move', [AWSController::class, 'move']);
Route::post('/remove', [AWSController::class, 'remove']);
Route::prefix('/auth')->group(function() {
    Route::post('/login', [AuthController::class, 'login'])
    ->middleware([
        'Auth.Login'
    ]);
});
