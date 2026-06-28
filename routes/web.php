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

            // 3. Todos los equipos reales confirmados (FIFA 2026)
            $sudafrica  = $real('za');      // South Africa
            $canada     = $real('ca');      // Canada
            $brasil     = $real('br');      // Brazil
            $japon      = $real('jp');      // Japan
            $alemania   = $real('de');      // Germany
            $paraguay   = $real('py');      // Paraguay
            $paises     = $real('nl');      // Netherlands
            $marruecos  = $real('ma');      // Morocco
            $marfil     = $real('ci');      // Cote d'Ivoire
            $noruega    = $real('no');      // Norway
            $francia    = $real('fr');      // France
            $suecia     = $real('se');      // Sweden
            $mexico     = $real('mx');      // Mexico
            $ecuador    = $real('ec');      // Ecuador
            $inglaterra = $real('gb-eng');  // England
            $rdCongo    = $real('cd');      // DR Congo
            $belgica    = $real('be');      // Belgium
            $senegal    = $real('sn');      // Senegal
            $usa        = $real('us');      // USA
            $bosnia     = $real('ba');      // Bosnia and Herzegovina
            $espana     = $real('es');      // Spain
            $austria    = $real('at');      // Austria
            $portugal   = $real('pt');      // Portugal
            $croacia    = $real('hr');      // Croatia
            $suiza      = $real('ch');      // Switzerland
            $argelia    = $real('dz');      // Algeria
            $australia  = $real('au');      // Australia
            $egipto     = $real('eg');      // Egypt
            $argentina  = $real('ar');      // Argentina
            $caboVerde  = $real('cv');      // Cabo Verde
            $colombia   = $real('co');      // Colombia
            $ghana      = $real('gh');      // Ghana

            // 4. Partidos R32 — Horario OFICIAL Guatemala (FIFA 2026)
            $matches = [
                // Dom 28 jun
                [$sudafrica,  $canada,     '2026-06-28 13:00:00'],  // South Africa vs Canada      1:00 PM
                // Lun 29 jun
                [$brasil,     $japon,      '2026-06-29 11:00:00'],  // Brazil vs Japan             11:00 AM
                [$alemania,   $paraguay,   '2026-06-29 14:30:00'],  // Germany vs Paraguay         2:30 PM
                [$paises,     $marruecos,  '2026-06-29 19:00:00'],  // Netherlands vs Morocco      7:00 PM
                // Mar 30 jun
                [$marfil,     $noruega,    '2026-06-30 11:00:00'],  // Cote d'Ivoire vs Norway     11:00 AM
                [$francia,    $suecia,     '2026-06-30 15:00:00'],  // France vs Sweden            3:00 PM
                [$mexico,     $ecuador,    '2026-06-30 19:00:00'],  // Mexico vs Ecuador           7:00 PM
                // Mie 1 jul
                [$inglaterra, $rdCongo,    '2026-07-01 10:00:00'],  // England vs DR Congo         10:00 AM
                [$belgica,    $senegal,    '2026-07-01 14:00:00'],  // Belgium vs Senegal          2:00 PM
                [$usa,        $bosnia,     '2026-07-01 18:00:00'],  // USA vs Bosnia               6:00 PM
                // Jue 2 jul
                [$espana,     $austria,    '2026-07-02 13:00:00'],  // Spain vs Austria            1:00 PM
                [$portugal,   $croacia,    '2026-07-02 17:00:00'],  // Portugal vs Croatia         5:00 PM
                [$suiza,      $argelia,    '2026-07-02 21:00:00'],  // Switzerland vs Algeria      9:00 PM
                // Vie 3 jul
                [$australia,  $egipto,     '2026-07-03 12:00:00'],  // Australia vs Egypt          12:00 PM
                [$argentina,  $caboVerde,  '2026-07-03 16:00:00'],  // Argentina vs Cabo Verde     4:00 PM
                [$colombia,   $ghana,      '2026-07-03 19:30:00'],  // Colombia vs Ghana           7:30 PM
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
