<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuinielaController extends Controller
{
    // ==========================================
    // LANDING & AUTHENTICATION
    // ==========================================

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('welcome');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'points'   => 0,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

    // ==========================================
    // USER DASHBOARD & PREDICTIONS
    // ==========================================

    public function dashboard()
    {
        $user = Auth::user();

        // Games sorted by date
        $games = Game::with(['homeTeam', 'awayTeam'])->orderBy('match_date', 'asc')->get();

        // Group games by stage
        $groupedGames = $games->groupBy(function ($game) {
            if ($game->stage === 'group') {
                return 'Grupo ' . ($game->homeTeam->group ?? 'A');
            }
            $stages = [
                'r32'         => 'Dieciseisavos de Final',
                'r16'         => 'Octavos de Final',
                'quarter'     => 'Cuartos de Final',
                'semi'        => 'Semifinales',
                'third_place' => 'Tercer Lugar',
                'final'       => '🏆 Gran Final',
            ];
            return $stages[$game->stage] ?? 'Fases Eliminatorias';
        });

        // Current user's predictions
        $predictions = Prediction::where('user_id', $user->id)->get()->keyBy('game_id');

        // Leaderboard (ordered by points, then name)
        $leaderboard = User::orderBy('points', 'desc')->orderBy('name', 'asc')->get();

        // Champion pick data
        $realTeams       = Team::whereNotIn('group', ['TBD'])->whereNotNull('group')->orderBy('group')->orderBy('name')->get();
        $worldChampionId = Setting::getValue('world_cup_champion');
        $worldChampion   = $worldChampionId ? Team::find($worldChampionId) : null;
        $userChampionPick = $user->championPick;

        // --- DEADLINE DINÁMICA (#1 y #8): Lee el primer partido real de la BD ---
        $firstGame = Game::where('stage', 'group')->orderBy('match_date', 'asc')->first();
        $championDeadline   = $firstGame ? $firstGame->match_date : Carbon::parse('2026-06-11 13:00:00');
        $championPickClosed = Carbon::now()->isAfter($championDeadline);

        // Unlocked stages based on finished games
        $unlockedStages = Game::getUnlockedStages();

        // --- HISTORIAL DE PREDICCIONES (#2): Partidos finalizados con predicciones ---
        $finishedGames = $games->where('status', 'finished')->values();
        $predictionHistory = $finishedGames->map(function ($game) use ($predictions) {
            return [
                'game'       => $game,
                'prediction' => $predictions->get($game->id),
            ];
        });
        $historyStats = [
            'total'     => $predictionHistory->count(),
            'predicted' => $predictionHistory->filter(fn($r) => $r['prediction'])->count(),
            'exact'     => $predictionHistory->filter(fn($r) => $r['prediction'] && $r['prediction']->points_earned == 3)->count(),
            'correct'   => $predictionHistory->filter(fn($r) => $r['prediction'] && $r['prediction']->points_earned == 1)->count(),
            'wrong'     => $predictionHistory->filter(fn($r) => $r['prediction'] && $r['prediction']->points_earned == 0)->count(),
            'missed'    => $predictionHistory->filter(fn($r) => !$r['prediction'])->count(),
        ];

        // --- TABLA DE POSICIONES POR GRUPO (#7) ---
        $groupStandings = $this->calculateGroupStandings($games);

        return view('dashboard', compact(
            'groupedGames',
            'predictions',
            'leaderboard',
            'realTeams',
            'worldChampion',
            'userChampionPick',
            'championPickClosed',
            'championDeadline',
            'worldChampionId',
            'unlockedStages',
            'predictionHistory',
            'historyStats',
            'groupStandings'
        ));
    }

    public function savePredictions(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'predictions'                => 'required|array',
            'predictions.*.game_id'      => 'required|exists:games,id',
            'predictions.*.home_score'   => 'required|integer|min:0',
            'predictions.*.away_score'   => 'required|integer|min:0',
        ]);

        $count = 0;
        $now   = Carbon::now();
        $unlockedStages = Game::getUnlockedStages();

        foreach ($request->predictions as $predData) {
            $game = Game::findOrFail($predData['game_id']);

            // Critical Rule: Cannot predict if match has started
            if ($game->match_date->isBefore($now)) {
                continue;
            }

            // Critical Rule: Cannot predict if stage is not unlocked
            if (!in_array($game->stage, $unlockedStages)) {
                continue;
            }

            Prediction::updateOrCreate(
                ['user_id' => $user->id, 'game_id' => $game->id],
                ['home_score' => $predData['home_score'], 'away_score' => $predData['away_score']]
            );
            $count++;
        }

        return redirect()->route('dashboard')->with('success', "$count pronósticos guardados exitosamente.");
    }

    // ==========================================
    // CHAMPION PICK
    // ==========================================

    public function saveChampionPick(Request $request)
    {
        $request->validate([
            'champion_team_id' => 'required|exists:teams,id',
        ]);

        // Deadline dinámica: lee primer partido de la BD
        $firstGame = Game::where('stage', 'group')->orderBy('match_date', 'asc')->first();
        $deadline  = $firstGame ? $firstGame->match_date : Carbon::parse('2026-06-11 13:00:00');

        if (Carbon::now()->isAfter($deadline)) {
            return back()->with('error', 'La selección del Campeón del Mundial ya está cerrada (el torneo ha comenzado).');
        }

        $user = Auth::user();
        $user->champion_pick_team_id = $request->champion_team_id;
        $user->save();

        $team = Team::find($request->champion_team_id);
        return back()->with('success', "✅ Seleccionaste a {$team->name} como Campeón del Mundial. ¡Suerte!");
    }

    // ==========================================
    // ADMIN PANEL
    // ==========================================

    public function adminDashboard()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $games = Game::with(['homeTeam', 'awayTeam'])->orderBy('match_date', 'asc')->get();
        $groupedGames = $games->groupBy('stage');

        // Champion data for admin
        $realTeams       = Team::whereNotIn('group', ['TBD'])->whereNotNull('group')->orderBy('group')->orderBy('name')->get();
        $worldChampionId = Setting::getValue('world_cup_champion');
        $worldChampion   = $worldChampionId ? Team::find($worldChampionId) : null;

        return view('admin.dashboard', compact('groupedGames', 'realTeams', 'worldChampion', 'worldChampionId'));
    }

    public function bulkUpdateGames(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $request->validate([
            'games' => 'required|array',
            'games.*.home_score'   => 'nullable|integer|min:0',
            'games.*.away_score'   => 'nullable|integer|min:0',
            'games.*.home_team_id' => 'nullable|exists:teams,id',
            'games.*.away_team_id' => 'nullable|exists:teams,id',
            'games.*.winner_id'    => 'nullable|exists:teams,id',
        ]);

        $updatedCount = 0;
        $gamesToRecalculate = [];

        foreach ($request->games as $gameId => $data) {
            $game = Game::findOrFail($gameId);
            $changed = false;

            if (isset($data['home_team_id']) && $game->home_team_id != $data['home_team_id']) {
                $game->home_team_id = $data['home_team_id'];
                $changed = true;
            }
            if (isset($data['away_team_id']) && $game->away_team_id != $data['away_team_id']) {
                $game->away_team_id = $data['away_team_id'];
                $changed = true;
            }
            if (isset($data['winner_id']) && $game->winner_id != $data['winner_id']) {
                $game->winner_id = $data['winner_id'] ?: null;
                $changed = true;
            }

            // Check if scores are filled to mark as finished
            if (isset($data['home_score']) && $data['home_score'] !== null && $data['home_score'] !== '' && 
                isset($data['away_score']) && $data['away_score'] !== null && $data['away_score'] !== '') {
                if ($game->home_score !== (int)$data['home_score'] || $game->away_score !== (int)$data['away_score'] || $game->status !== 'finished') {
                    $game->home_score = (int)$data['home_score'];
                    $game->away_score = (int)$data['away_score'];
                    $game->status     = 'finished';
                    $changed = true;
                    $gamesToRecalculate[] = $game;
                }
            } else {
                if ($game->status === 'finished') {
                    $game->home_score = null;
                    $game->away_score = null;
                    $game->status     = 'pending';
                    $changed = true;
                }
            }

            if ($changed) {
                $game->save();
                $updatedCount++;
            }
        }

        // Recalculate points if any game result changed
        if (count($gamesToRecalculate) > 0) {
            foreach ($gamesToRecalculate as $game) {
                $predictions = Prediction::where('game_id', $game->id)->get();
                foreach ($predictions as $prediction) {
                    $points = 0;
                    $realHome = $game->home_score;
                    $realAway = $game->away_score;
                    $predHome = $prediction->home_score;
                    $predAway = $prediction->away_score;

                    if ($realHome === $predHome && $realAway === $predAway) {
                        $points = 3;
                    } else {
                        $realOutcome = ($realHome > $realAway) ? 1 : (($realHome < $realAway) ? -1 : 0);
                        $predOutcome = ($predHome > $predAway) ? 1 : (($predHome < $predAway) ? -1 : 0);
                        if ($realOutcome === $predOutcome) {
                            $points = 1;
                        }
                    }
                    $prediction->points_earned = $points;
                    $prediction->save();
                }
            }

            // Recalculate user totals
            $allUsers = User::all();
            foreach ($allUsers as $u) {
                $predPoints = Prediction::where('user_id', $u->id)
                    ->whereNotNull('points_earned')
                    ->sum('points_earned');
                $championBonus = $u->champion_points_awarded ? 50 : 0;
                $u->points = $predPoints + $championBonus + $u->extra_points;
                $u->save();
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', "Se actualizaron $updatedCount partidos con éxito.")
            ->with('show_print_stage', $request->input('stage_key'));
    }

    public function printResults(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::check()) {
            return redirect()->route('login');
        }

        $stage = $request->query('stage');
        $query = Game::with(['homeTeam', 'awayTeam'])->orderBy('match_date', 'asc');
        
        if ($stage) {
            $query->where('stage', $stage);
        }

        $games = $query->get();

        $stageTitles = [
            'group'       => 'Fase de Grupos',
            'r32'         => 'Dieciseisavos de Final',
            'r16'         => 'Octavos de Final',
            'quarter'     => 'Cuartos de Final',
            'semi'        => 'Semifinales',
            'third_place' => 'Tercer Lugar',
            'final'       => 'Gran Final',
        ];

        $title = 'Reporte de Resultados - ' . ($stageTitles[$stage] ?? 'Todos los Partidos');

        return view('admin.print_results', compact('games', 'title', 'stage'));
    }

    public function updateGameResult(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $game = Game::findOrFail($id);

        $request->validate([
            'home_score'   => 'nullable|integer|min:0',
            'away_score'   => 'nullable|integer|min:0',
            'home_team_id' => 'nullable|exists:teams,id',
            'away_team_id' => 'nullable|exists:teams,id',
            'winner_id'    => 'nullable|exists:teams,id',
        ]);

        if ($request->has('home_team_id')) {
            $game->home_team_id = $request->home_team_id;
        }
        if ($request->has('away_team_id')) {
            $game->away_team_id = $request->away_team_id;
        }
        if ($request->has('winner_id')) {
            $game->winner_id = $request->winner_id ?: null;
        }

        if ($request->filled('home_score') && $request->filled('away_score')) {
            $game->home_score = $request->home_score;
            $game->away_score = $request->away_score;
            $game->status     = 'finished';
            $game->save();

            // Calculate points for all predictions of this game
            $predictions = Prediction::where('game_id', $game->id)->get();

            foreach ($predictions as $prediction) {
                $points = 0;

                $realHome = $game->home_score;
                $realAway = $game->away_score;
                $predHome = $prediction->home_score;
                $predAway = $prediction->away_score;

                // Exact score = 3 points
                if ($realHome === $predHome && $realAway === $predAway) {
                    $points = 3;
                } else {
                    // Correct outcome = 1 point
                    $realOutcome = ($realHome > $realAway) ? 1 : (($realHome < $realAway) ? -1 : 0);
                    $predOutcome = ($predHome > $predAway) ? 1 : (($predHome < $predAway) ? -1 : 0);

                    if ($realOutcome === $predOutcome) {
                        $points = 1;
                    }
                }

                $prediction->points_earned = $points;
                $prediction->save();
            }

            // Recalculate users' total points (predictions only, champion bonus handled separately)
            $allUsers = User::all();
            foreach ($allUsers as $u) {
                $predPoints = Prediction::where('user_id', $u->id)
                    ->whereNotNull('points_earned')
                    ->sum('points_earned');

                // Add champion bonus if already awarded
                $championBonus = $u->champion_points_awarded ? 50 : 0;
                $u->points     = $predPoints + $championBonus + $u->extra_points;
                $u->save();
            }
        } else {
            $game->save();
        }

        return redirect()->route('admin.dashboard')->with('success', "Información del partido actualizada exitosamente.");
    }

    public function declareChampion(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $request->validate([
            'champion_team_id' => 'required|exists:teams,id',
        ]);

        $championId = $request->champion_team_id;

        // Save champion to settings
        Setting::setValue('world_cup_champion', $championId);

        // Award 50 points to users who picked correctly (only once)
        $winners = User::where('champion_pick_team_id', $championId)
                       ->where('champion_points_awarded', false)
                       ->get();

        foreach ($winners as $u) {
            $u->points += 50;
            $u->champion_points_awarded = true;
            $u->save();
        }

        $team = Team::find($championId);
        $count = $winners->count();
        return back()->with('success', "🏆 Campeón declarado: {$team->name}. Se otorgaron 50 pts a {$count} participante(s).");
    }

    // ==========================================
    // HELPER: TABLA DE POSICIONES POR GRUPO (#7)
    // ==========================================

    private function calculateGroupStandings($games): array
    {
        $standings = [];
        $groupGames = $games->where('stage', 'group')->where('status', 'finished');

        foreach ($groupGames as $game) {
            $homeTeam = $game->homeTeam;
            $awayTeam = $game->awayTeam;

            if (!$homeTeam || !$awayTeam) continue;
            if ($homeTeam->group === 'TBD' || $awayTeam->group === 'TBD') continue;

            $group = $homeTeam->group;
            $hId = $homeTeam->id;
            $aId = $awayTeam->id;

            // Init entries
            foreach ([$homeTeam, $awayTeam] as $team) {
                $tid = $team->id;
                if (!isset($standings[$group][$tid])) {
                    $standings[$group][$tid] = [
                        'team' => $team,
                        'pj'   => 0, 'g' => 0, 'e' => 0, 'p' => 0,
                        'gf'   => 0, 'gc' => 0, 'pts' => 0,
                    ];
                }
            }

            $hg = $game->home_score;
            $ag = $game->away_score;

            $standings[$group][$hId]['pj']++;
            $standings[$group][$aId]['pj']++;
            $standings[$group][$hId]['gf'] += $hg;
            $standings[$group][$hId]['gc'] += $ag;
            $standings[$group][$aId]['gf'] += $ag;
            $standings[$group][$aId]['gc'] += $hg;

            if ($hg > $ag) {
                $standings[$group][$hId]['g']++;   $standings[$group][$hId]['pts'] += 3;
                $standings[$group][$aId]['p']++;
            } elseif ($ag > $hg) {
                $standings[$group][$aId]['g']++;   $standings[$group][$aId]['pts'] += 3;
                $standings[$group][$hId]['p']++;
            } else {
                $standings[$group][$hId]['e']++;   $standings[$group][$hId]['pts']++;
                $standings[$group][$aId]['e']++;   $standings[$group][$aId]['pts']++;
            }
        }

        // Sort each group: pts DESC, gd DESC, gf DESC
        foreach ($standings as $group => &$teams) {
            usort($teams, function ($a, $b) {
                $gdA = $a['gf'] - $a['gc'];
                $gdB = $b['gf'] - $b['gc'];
                if ($b['pts'] !== $a['pts']) return $b['pts'] - $a['pts'];
                if ($gdB !== $gdA) return $gdB - $gdA;
                return $b['gf'] - $a['gf'];
            });
        }
        unset($teams);

        ksort($standings);
        return $standings;
    }

    // ==========================================
    // EXPORTAR PREDICCIONES CSV (#10)
    // ==========================================

    public function exportPredictions()
    {
        $user  = Auth::user();
        $games = Game::with(['homeTeam', 'awayTeam'])
                     ->where('status', 'finished')
                     ->orderBy('match_date', 'asc')
                     ->get();

        $predictions = Prediction::where('user_id', $user->id)
                                 ->get()->keyBy('game_id');

        $filename = 'mis_pronosticos_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($games, $predictions) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Fecha', 'Partido', 'Mi Pronóstico', 'Resultado Real', 'Puntos']);

            foreach ($games as $game) {
                $pred = $predictions->get($game->id);
                $home = $game->homeTeam->name ?? '?';
                $away = $game->awayTeam->name ?? '?';

                fputcsv($handle, [
                    $game->match_date->format('d/m/Y H:i'),
                    "{$home} vs {$away}",
                    $pred ? "{$pred->home_score} - {$pred->away_score}" : 'Sin pronóstico',
                    $game->home_score . ' - ' . $game->away_score,
                    $pred ? ($pred->points_earned ?? 0) : 0,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // MANAGE USERS
    // ==========================================

    public function usersDashboard()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $users = User::orderBy('points', 'desc')->orderBy('name', 'asc')->get();
        $realTeams = Team::whereNotIn('group', ['TBD'])->whereNotNull('group')->orderBy('name')->get();

        return view('admin.users', compact('users', 'realTeams'));
    }

    public function updateUserAdmin(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'extra_points' => 'required|integer',
            'champion_pick_team_id' => 'nullable|exists:teams,id',
        ]);

        $user->extra_points = $request->extra_points;
        if ($request->has('champion_pick_team_id')) {
            $user->champion_pick_team_id = $request->champion_pick_team_id;
        }

        // Recalculate total points
        $predPoints = Prediction::where('user_id', $user->id)
            ->whereNotNull('points_earned')
            ->sum('points_earned');
        $championBonus = $user->champion_points_awarded ? 50 : 0;
        $user->points = $predPoints + $championBonus + $user->extra_points;

        $user->save();

        return redirect()->route('admin.users.index')->with('success', "Usuario {$user->name} actualizado exitosamente.");
    }
}
