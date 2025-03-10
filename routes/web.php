<?php

use App\Http\Controllers\AiController;
use Illuminate\Support\Facades\Route;

Route::controller(AiController::class)
    ->prefix('/ai-request')
    ->name('ai-request')
    ->group(function() {
        Route::post('', 'index');
    });