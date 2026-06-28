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

    // RUTA PARA ELIMINAR DUPLICADOS — versión simple y directa
    Route::get('/admin/limpiar-duplicados', function () {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return 'Acceso denegado.';
        }

        try {
            // Cargar todos los juegos ordenados por ID (el más bajo = el original)
            $games = \App\Models\Game::orderBy('id', 'asc')->get();

            $seen      = []; // key => id del juego original a conservar
            $toDelete  = []; // IDs de duplicados a eliminar

            foreach ($games as $game) {
                $key = $game->home_team_id . '-' . $game->away_team_id;
                if (isset($seen[$key])) {
                    // Es un duplicado — marcarlo para borrar
                    $toDelete[] = $game->id;
                } else {
                    $seen[$key] = $game->id;
                }
            }

            $deleted = count($toDelete);

            if ($deleted > 0) {
                // Borrar predicciones huérfanas de los duplicados
                \App\Models\Prediction::whereIn('game_id', $toDelete)->delete();
                // Borrar los partidos duplicados
                \App\Models\Game::whereIn('id', $toDelete)->delete();
            }

            $list = implode(', ', $toDelete);
            return "✅ Eliminados {$deleted} partido(s) duplicado(s) (IDs: {$list}). Recarga el dashboard.";
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    // ══════════════════════════════════════════════════════
    // RUTA: RECREAR DIECISEISAVOS DE FINAL (FIFA 2026 oficial)
    // Horarios en hora Guatemala. Equipos reales donde se conocen.
    // ══════════════════════════════════════════════════════
    Route::get('/admin/fix-r32', function () {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return 'Acceso denegado.';
        }

        try {
            // 1. Borrar pronósticos y partidos R32 existentes
            $oldR32Ids = \App\Models\Game::where('stage', 'r32')->pluck('id');
            \App\Models\Prediction::whereIn('game_id', $oldR32Ids)->delete();
            \App\Models\Game::where('stage', 'r32')->delete();

            // 2. Helper: obtener equipo real por code
            $real = fn($code) => \App\Models\Team::where('code', $code)->firstOrFail();

            // 3. Helper: crear/actualizar placeholder TBD
            $tbd = fn($name, $code) => \App\Models\Team::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'group' => 'TBD']
            );

            // 4. Equipos reales confirmados
            $sudafrica  = $real('za');   // Sudáfrica
            $canada     = $real('ca');   // Canadá
            $brasil     = $real('br');   // Brasil
            $japon      = $real('jp');   // Japón
            $alemania   = $real('de');   // Alemania
            $paraguay   = $real('py');   // Paraguay
            $paises     = $real('nl');   // Países Bajos
            $marruecos  = $real('ma');   // Marruecos
            $marfil     = $real('ci');   // Costa de Marfil
            $noruega    = $real('no');   // Noruega
            $francia    = $real('fr');   // Francia
            $suecia     = $real('se');   // Suecia
            $mexico     = $real('mx');   // México
            $belgica    = $real('be');   // Bélgica
            $usa        = $real('us');   // EE.UU.
            $bosnia     = $real('ba');   // Bosnia y Herzegovina
            $espana     = $real('es');   // España
            $suiza      = $real('ch');   // Suiza
            $australia  = $real('au');   // Australia
            $egipto     = $real('eg');   // Egipto
            $argentina  = $real('ar');   // Argentina
            $caboVerde  = $real('cv');   // Cabo Verde

            // 5. Placeholders para cruces aún TBD
            $t3cefhi  = $tbd('3° C/E/F/H/I',  'tbd-3cefhi');
            $t1L      = $tbd('1° Grupo L',     'tbd-1l');
            $t3ehijk  = $tbd('3° E/H/I/J/K',  'tbd-3ehijk');
            $t3aehij  = $tbd('3° A/E/H/I/J',  'tbd-3aehij');
            $t2J      = $tbd('2° Grupo J',     'tbd-2j');
            $t2K      = $tbd('2° Grupo K',     'tbd-2k');
            $t2L      = $tbd('2° Grupo L',     'tbd-2l');
            $t3efgij  = $tbd('3° E/F/G/I/J',  'tbd-3efgij');
            $t1K      = $tbd('1° Grupo K',     'tbd-1k');
            $t3deijl  = $tbd('3° D/E/I/J/L',  'tbd-3deijl');

            // 6. Partidos R32 — Horario oficial Guatemala
            $matches = [
                // Dom 28 jun
                [$sudafrica, $canada,    '2026-06-28 13:00:00'],  // Sudáfrica vs Canadá
                // Lun 29 jun
                [$brasil,    $japon,     '2026-06-29 13:00:00'],  // Brasil vs Japón
                [$alemania,  $paraguay,  '2026-06-29 14:30:00'],  // Alemania vs Paraguay
                [$paises,    $marruecos, '2026-06-29 19:00:00'],  // Países Bajos vs Marruecos
                // Mar 30 jun
                [$marfil,    $noruega,   '2026-06-30 11:00:00'],  // Costa de Marfil vs Noruega
                [$francia,   $suecia,    '2026-06-30 15:00:00'],  // Francia vs Suecia
                [$mexico,    $t3cefhi,   '2026-06-30 19:00:00'],  // México vs 3°C/E/F/H/I
                // Mié 1 jul
                [$t1L,       $t3ehijk,   '2026-07-01 10:00:00'],  // 1°L vs 3°E/H/I/J/K
                [$belgica,   $t3aehij,   '2026-07-01 14:00:00'],  // Bélgica vs 3°A/E/H/I/J
                [$usa,       $bosnia,    '2026-07-01 18:00:00'],  // EE.UU. vs Bosnia y Herzegovina
                // Jue 2 jul
                [$espana,    $t2J,       '2026-07-02 13:00:00'],  // España vs 2°J
                [$t2K,       $t2L,       '2026-07-02 17:00:00'],  // 2°K vs 2°L
                [$suiza,     $t3efgij,   '2026-07-02 21:00:00'],  // Suiza vs 3°E/F/G/I/J
                // Vie 3 jul
                [$australia, $egipto,    '2026-07-03 12:00:00'],  // Australia vs Egipto
                [$argentina, $caboVerde, '2026-07-03 16:00:00'],  // Argentina vs Cabo Verde
                [$t1K,       $t3deijl,   '2026-07-03 19:30:00'],  // 1°K vs 3°D/E/I/J/L
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

            $total = count($matches);
            return "✅ Dieciseisavos de final recreados: {$total} partidos con horarios y equipos oficiales FIFA 2026. Recarga el dashboard.";

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
        }
    });
});
