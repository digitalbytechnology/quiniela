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

    // Admin Users routes
    Route::get('/admin/users',             [QuinielaController::class, 'usersDashboard'])->name('admin.users.index');
    Route::post('/admin/users/{id}/update',[QuinielaController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/admin/users/{id}',     [QuinielaController::class, 'deleteUserAdmin'])->name('admin.users.delete');
    Route::post('/admin/users/{id}/reset-password', [QuinielaController::class, 'resetUserPassword'])->name('admin.users.reset_password');

    // RUTA TEMPORAL PARA ARREGLAR DUPLICADOS EN PRODUCCIÓN
    Route::get('/admin/fix-duplicados', function () {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return 'Acceso denegado.';
        }
        
        try {
            // Limpiar OPcache y caché de Laravel para forzar a leer el código nuevo
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');

            // Borrar todos los partidos y resembrar
            \App\Models\Game::query()->delete();
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'WorldCupSeeder',
                '--force' => true
            ]);
            
            return '¡Caché limpiada y partidos re-creados exitosamente! Por favor recarga el dashboard y verifica.';
        } catch (\Exception $e) {
            return 'Error al limpiar base de datos: ' . $e->getMessage();
        }
    });

    // RUTA PARA ELIMINAR DUPLICADOS SIN BORRAR TODO
    Route::get('/admin/limpiar-duplicados', function () {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return 'Acceso denegado.';
        }

        try {
            $games = \App\Models\Game::orderBy('id', 'asc')->get();

            // Agrupar por home_team_id + away_team_id
            $grouped = $games->groupBy(fn($g) => $g->home_team_id . '-' . $g->away_team_id);

            $deleted = 0;
            foreach ($grouped as $key => $duplicates) {
                if ($duplicates->count() <= 1) continue;

                // Conservar el que tiene resultados (finished) o el de menor ID
                $sorted = $duplicates->sortByDesc(fn($g) => $g->status === 'finished' ? 1 : 0)->values();

                $toKeep = $sorted->first();
                foreach ($sorted->slice(1) as $dup) {
                    // Reasignar predicciones al juego que conservamos si no hay conflicto
                    \App\Models\Prediction::where('game_id', $dup->id)
                        ->whereNotExists(function ($q) use ($toKeep) {
                            $q->from('predictions as p2')
                              ->whereColumn('p2.user_id', 'predictions.user_id')
                              ->where('p2.game_id', $toKeep->id);
                        })
                        ->update(['game_id' => $toKeep->id]);

                    // Borrar predicciones huérfanas del duplicado
                    \App\Models\Prediction::where('game_id', $dup->id)->delete();

                    // Borrar el partido duplicado
                    $dup->delete();
                    $deleted++;
                }
            }

            return "✅ Limpieza completada. Se eliminaron {$deleted} partido(s) duplicado(s). Recarga el dashboard.";
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });
});
