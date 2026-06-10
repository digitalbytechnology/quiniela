<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuinielaController;

// Public routes
Route::get('/', [QuinielaController::class, 'index'])->name('landing');

Route::get('/login',  [QuinielaController::class, 'showLogin'])->name('login');
Route::post('/login', [QuinielaController::class, 'login']);

Route::get('/register',  [QuinielaController::class, 'showRegister'])->name('register');
Route::post('/register', [QuinielaController::class, 'register']);

// Protected routes (requires login)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [QuinielaController::class, 'logout'])->name('logout');

    Route::get('/dashboard',     [QuinielaController::class, 'dashboard'])->name('dashboard');
    Route::post('/predictions',  [QuinielaController::class, 'savePredictions'])->name('predictions.save');

    // Champion pick
    Route::post('/champion-pick', [QuinielaController::class, 'saveChampionPick'])->name('champion.pick');

    // Export CSV (#10)
    Route::get('/export/predictions', [QuinielaController::class, 'exportPredictions'])->name('export.predictions');

    // Admin routes
    Route::get('/admin',                   [QuinielaController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::post('/admin/game/{id}',        [QuinielaController::class, 'updateGameResult'])->name('admin.game.update');
    Route::post('/admin/games/bulk-update', [QuinielaController::class, 'bulkUpdateGames'])->name('admin.games.bulk_update');
    Route::get('/admin/print-results',     [QuinielaController::class, 'printResults'])->name('admin.print_results');
    Route::post('/admin/declare-champion', [QuinielaController::class, 'declareChampion'])->name('admin.champion.declare');
});
