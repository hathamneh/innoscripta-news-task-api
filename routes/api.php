<?php

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', \App\Http\Controllers\User\UserController::class);
    Route::get('/user/settings', [\App\Http\Controllers\User\UserSettingsController::class, 'index']);
    Route::put('/user/settings', [\App\Http\Controllers\User\UserSettingsController::class, 'update']);
});

Route::get('/articles', [\App\Http\Controllers\News\ArticleController::class, 'index']);

Route::get('/sources', [\App\Http\Controllers\News\NewsSourceController::class, 'index']);
Route::get('/sources/lookup', [\App\Http\Controllers\News\NewsSourceController::class, 'lookup']);
Route::get('/sources/{source}', [\App\Http\Controllers\News\NewsSourceController::class, 'show']);

Route::get('/categories', [\App\Http\Controllers\News\CategoryController::class, 'index']);
Route::get('/categories/lookup', [\App\Http\Controllers\News\CategoryController::class, 'lookup']);

Route::get('/countries/lookup', [\App\Http\Controllers\CountryController::class, 'lookup']);

Route::get('/languages/lookup', [\App\Http\Controllers\LanguageController::class, 'lookup']);

