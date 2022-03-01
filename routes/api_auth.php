<?php

use App\Http\Controllers\User\UserBrowseController;
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

Route::prefix('/user')->group(function() {
    Route::get('/', [UserBrowseController::class, 'Anything'])->middleware(['QueryRoute']);
    Route::get('/{query}', [UserBrowseController::class, 'Anything'])->where('query', '.*')->middleware(['QueryRoute']);
});
