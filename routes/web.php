<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\BuildController;
use Illuminate\Support\Facades\Route;

Route::controller(AiController::class)
    ->prefix('/ai-request')
    ->name('ai-request')
    ->group(function() {
        Route::post('', 'index');
    });

Route::controller(BuildController::class)
    ->prefix('/build')
    ->name('build')
    ->group(function() {
        Route::post('{schemaId}', 'index');
    });