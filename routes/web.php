<?php

declare(strict_types=1);

use App\Http\Controllers\AiController;
use App\Http\Controllers\BuildController;
use Illuminate\Support\Facades\Route;

Route::controller(AiController::class)
    ->prefix('/ai-request')
    ->name('ai-request.')
    ->group(function() {
        Route::post('', 'index')->name('request');
        Route::post('correct/{schemaId}', 'correct')->name('correct');
    });

Route::controller(BuildController::class)->prefix('/build')
    ->group(function() {
        Route::post('{schemaId}', 'index')->name('build');
    });

require __DIR__.'/auth.php';
