<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CauLacBoApiController;
use App\Http\Controllers\Api\SuKienApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register'])->name('register');
    Route::post('/login', [AuthApiController::class, 'login'])->name('login');
    Route::get('/me', [AuthApiController::class, 'me'])->name('me');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
});

Route::apiResource('caulacbo', CauLacBoApiController::class)->names('api.caulacbo');
Route::apiResource('sukien', SuKienApiController::class)->names('api.sukien');
