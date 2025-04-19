<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\Auth\CentrifugoController;
use App\Http\Controllers\Auth\ForgotController;
use App\Http\Controllers\Auth\RegisterController;
use App\MoonShine\Pages\Auth\ResetPasswordPage;
use Illuminate\Support\Facades\Route;

Route::controller(AuthenticateController::class)->group(function () {
    Route::get('/login', 'form')->middleware(['guest', 'lang'])->name('login');
    Route::post('/login', 'authenticate')->middleware(['guest', 'lang'])->name('authenticate');
    Route::get('/logout', 'logout')->middleware('moonshine')->name('logout');
});

Route::controller(ForgotController::class)->middleware(['guest', 'lang'])->group(function () {
    //Route::get('/forgot', 'form')->name('forgot');
    Route::post('/forgot', 'reset')->name('forgot.reset');
    Route::get('/reset-password/{token}', static fn (ResetPasswordPage $page) => $page)->name('password.reset');
    Route::post('/reset-password', 'updatePassword')->name('password.update');
});

Route::controller(RegisterController::class)->middleware(['guest', 'lang'])->group(function () {
    Route::get('/register', 'form')->name('register');
    Route::post('/register', 'store')->name('register.store');
});

Route::controller(CentrifugoController::class)->middleware('moonshine')->group(function () {
    Route::post('/centrifugo/token', 'index')->name('centrifugo.token');
});
