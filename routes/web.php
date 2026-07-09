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
    Route::post('/admin/game/{id}/schedule', [QuinielaController::class, 'updateGameSchedule'])->name('admin.game.schedule');
    Route::get('/admin/print-results',     [QuinielaController::class, 'printResults'])->name('admin.print_results');
    Route::post('/admin/declare-champion', [QuinielaController::class, 'declareChampion'])->name('admin.champion.declare');

    // Admin Users routes
    Route::get('/admin/users',             [QuinielaController::class, 'usersDashboard'])->name('admin.users.index');
    Route::post('/admin/users/{id}/update',[QuinielaController::class, 'updateUserAdmin'])->name('admin.users.update');
    Route::delete('/admin/users/{id}',     [QuinielaController::class, 'deleteUserAdmin'])->name('admin.users.delete');
    Route::post('/admin/users/{id}/reset-password', [QuinielaController::class, 'resetUserPassword'])->name('admin.users.reset_password');

    // ══════════════════════════════════════════════════════════════════
    // RUTA: Borrar fase de grupos + activar R32 con equipos reales
    // Preserva los puntos acumulados de los usuarios (users.points).
    // ══════════════════════════════════════════════════════════════════
    Route::get('/admin/setup-r32', function () {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return 'Acceso denegado.';
        }
        try {
            // 1. Borrar predicciones y partidos de fase de grupos
            $groupIds = \App\Models\Game::where('stage', 'group')->pluck('id');
            \App\Models\Prediction::whereIn('game_id', $groupIds)->delete();
            $groupDel = \App\Models\Game::where('stage', 'group')->delete();

            // 2. Borrar R32 placeholders
            $r32Ids = \App\Models\Game::where('stage', 'r32')->pluck('id');
            \App\Models\Prediction::whereIn('game_id', $r32Ids)->delete();
            \App\Models\Game::where('stage', 'r32')->delete();

            // 3. Helper por code FIFA
            $real = fn($code) => \App\Models\Team::where('code', $code)->firstOrFail();

            // 4. Insertar 16 partidos R32 oficiales — Hora Guatemala
            $matches = [
                [$real('za'), $real('ca'),     '2026-06-28 13:00:00'], // Sudáfrica vs Canadá
                [$real('br'), $real('jp'),     '2026-06-29 11:00:00'], // Brasil vs Japón
                [$real('de'), $real('py'),     '2026-06-29 14:30:00'], // Alemania vs Paraguay
                [$real('nl'), $real('ma'),     '2026-06-29 19:00:00'], // Países Bajos vs Marruecos
                [$real('ci'), $real('no'),     '2026-06-30 11:00:00'], // Costa de Marfil vs Noruega
                [$real('fr'), $real('se'),     '2026-06-30 15:00:00'], // Francia vs Suecia
                [$real('mx'), $real('ec'),     '2026-06-30 19:00:00'], // México vs Ecuador
                [$real('gb-eng'), $real('cd'), '2026-07-01 10:00:00'], // Inglaterra vs RD Congo
                [$real('be'), $real('sn'),     '2026-07-01 14:00:00'], // Bélgica vs Senegal
                [$real('us'), $real('ba'),     '2026-07-01 18:00:00'], // Estados Unidos vs Bosnia
                [$real('es'), $real('at'),     '2026-07-02 13:00:00'], // España vs Austria
                [$real('pt'), $real('hr'),     '2026-07-02 17:00:00'], // Portugal vs Croacia
                [$real('ch'), $real('dz'),     '2026-07-02 21:00:00'], // Suiza vs Argelia
                [$real('au'), $real('eg'),     '2026-07-03 12:00:00'], // Australia vs Egipto
                [$real('ar'), $real('cv'),     '2026-07-03 16:00:00'], // Argentina vs Cabo Verde
                [$real('co'), $real('gh'),     '2026-07-03 19:30:00'], // Colombia vs Ghana
            ];

            foreach ($matches as $m) {
                \App\Models\Game::create([
                    'home_team_id' => $m[0]->id,
                    'away_team_id' => $m[1]->id,
                    'match_date'   => \Carbon\Carbon::parse($m[2]),
                    'stage'        => 'r32',
                    'status'       => 'pending',
                ]);
            }

            return "✅ Listo. Grupos borrados ({$groupDel}). 16 partidos de dieciseisavos activos. Puntos de usuarios intactos. Recarga el dashboard.";
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
        }
    });
});

// ══ RUTA DE EMERGENCIA (pública, sin login) — BORRAR DESPUÉS DEL MUNDIAL ══
Route::get('/setup-r32-now', function () {
    $secret = request('key');
    if ($secret !== 'mundial2026gt') {
        return 'Clave incorrecta.';
    }
    try {
        $groupIds = \App\Models\Game::where('stage', 'group')->pluck('id');
        \App\Models\Prediction::whereIn('game_id', $groupIds)->delete();
        $groupDel = \App\Models\Game::where('stage', 'group')->delete();

        $r32Ids = \App\Models\Game::where('stage', 'r32')->pluck('id');
        \App\Models\Prediction::whereIn('game_id', $r32Ids)->delete();
        \App\Models\Game::where('stage', 'r32')->delete();

        $real = fn($code) => \App\Models\Team::where('code', $code)->firstOrFail();

        $matches = [
            [$real('za'), $real('ca'),     '2026-06-28 13:00:00'],
            [$real('br'), $real('jp'),     '2026-06-29 11:00:00'],
            [$real('de'), $real('py'),     '2026-06-29 14:30:00'],
            [$real('nl'), $real('ma'),     '2026-06-29 19:00:00'],
            [$real('ci'), $real('no'),     '2026-06-30 11:00:00'],
            [$real('fr'), $real('se'),     '2026-06-30 15:00:00'],
            [$real('mx'), $real('ec'),     '2026-06-30 19:00:00'],
            [$real('gb-eng'), $real('cd'), '2026-07-01 10:00:00'],
            [$real('be'), $real('sn'),     '2026-07-01 14:00:00'],
            [$real('us'), $real('ba'),     '2026-07-01 18:00:00'],
            [$real('es'), $real('at'),     '2026-07-02 13:00:00'],
            [$real('pt'), $real('hr'),     '2026-07-02 17:00:00'],
            [$real('ch'), $real('dz'),     '2026-07-02 21:00:00'],
            [$real('au'), $real('eg'),     '2026-07-03 12:00:00'],
            [$real('ar'), $real('cv'),     '2026-07-03 16:00:00'],
            [$real('co'), $real('gh'),     '2026-07-03 19:30:00'],
        ];

        foreach ($matches as $m) {
            \App\Models\Game::create([
                'home_team_id' => $m[0]->id,
                'away_team_id' => $m[1]->id,
                'match_date'   => \Carbon\Carbon::parse($m[2]),
                'stage'        => 'r32',
                'status'       => 'pending',
            ]);
        }

        return "✅ Listo. Grupos borrados ({$groupDel}). 16 partidos de dieciseisavos activos. Puntos intactos.";
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

