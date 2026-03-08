<?php

use App\Http\Controllers\Survey\PublicSurveyController;
use App\Http\Controllers\Survey\SurveyController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Landing', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [SurveyController::class, 'index'])->name('dashboard');

    Route::prefix('surveys')->name('surveys.')->group(function (): void {
        Route::get('/', [SurveyController::class, 'library'])->name('index');
        Route::get('create', [SurveyController::class, 'create'])->name('create');
        Route::post('/', [SurveyController::class, 'store'])->name('store');
        Route::get('{survey}', [SurveyController::class, 'show'])->name('show');
        Route::get('{survey}/edit', [SurveyController::class, 'edit'])->name('edit');
        Route::put('{survey}', [SurveyController::class, 'update'])->name('update');
        Route::post('{survey}/publish', [SurveyController::class, 'publish'])->name('publish');
        Route::post('{survey}/close', [SurveyController::class, 'close'])->name('close');
        Route::delete('{survey}', [SurveyController::class, 'destroy'])->name('destroy');
    });
});

Route::prefix('s')->name('surveys.public.')->group(function (): void {
    Route::get('{survey:public_id}', [PublicSurveyController::class, 'access'])->name('access.show');
    Route::post('{survey:public_id}/access', [PublicSurveyController::class, 'verify'])->name('access.verify');
    Route::get('{survey:public_id}/respond', [PublicSurveyController::class, 'respond'])->name('respond');
    Route::post('{survey:public_id}/respond', [PublicSurveyController::class, 'submit'])->name('submit');
});
