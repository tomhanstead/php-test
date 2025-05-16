<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);

    // Secure task endpoints with token authentication and signed URLs
    Route::middleware(['task.token:edit', 'signed'])->group(function () {
        Route::put('/{id}/edit/{token}', [TaskController::class, 'secureUpdate'])
            ->name('tasks.edit');
    });

    Route::middleware(['task.token:delete', 'signed'])->group(function () {
        Route::delete('/{id}/delete/{token}', [TaskController::class, 'secureDestroy'])
            ->name('tasks.delete');
    });
});
