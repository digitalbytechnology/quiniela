<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;

// ── Antes de empezar: mostrar puntos actuales (para verificar que no cambien)
echo "=== PUNTOS ACTUALES (no deben cambiar) ===\n";
User::orderBy('points', 'desc')->get(['name','points'])->each(fn($u) => print("{$u->name}: {$u->points} pts\n"));

// ── 1. Borrar predicciones de partidos de grupo
$groupIds = Game::where('stage', 'group')->pluck('id');
$predsDel = Prediction::whereIn('game_id', $groupIds)->delete();
echo "\nPredicciones de grupos borradas: $predsDel\n";

// ── 2. Borrar partidos de grupo
$groupDel = Game::where('stage', 'group')->delete();
echo "Partidos de grupo borrados: $groupDel\n";

// ── 3. Borrar R32 actuales (placeholders)
$r32Ids = Game::where('stage', 'r32')->pluck('id');
Prediction::whereIn('game_id', $r32Ids)->delete();
$r32Del = Game::where('stage', 'r32')->delete();
echo "Partidos R32 anteriores borrados: $r32Del\n";

// ── 4. Insertar los 16 R32 reales
$T = [
    'Sudáfrica'            => 2,  'Canadá'               => 5,
    'Brasil'               => 9,  'Japón'                => 21,
    'Alemania'             => 17, 'Paraguay'             => 14,
    'Países Bajos'         => 22, 'Marruecos'            => 10,
    'Costa de Marfil'      => 19, 'Noruega'              => 36,
    'Francia'              => 33, 'Suecia'               => 23,
    'México'               => 1,  'Ecuador'              => 20,
    'Inglaterra'           => 45, 'RD Congo'             => 42,
    'Bélgica'              => 25, 'Senegal'              => 34,
    'Estados Unidos'       => 13, 'Bosnia y Herzegovina' => 6,
    'España'               => 29, 'Austria'              => 39,
    'Portugal'             => 43, 'Croacia'              => 46,
    'Suiza'                => 8,  'Argelia'              => 38,
    'Australia'            => 15, 'Egipto'               => 26,
    'Argentina'            => 37, 'Cabo Verde'           => 30,
    'Colombia'             => 41, 'Ghana'                => 47,
];

$r32 = [
    ['Sudáfrica',           'Canadá',              '2026-06-28 13:00:00'],
    ['Brasil',              'Japón',               '2026-06-29 11:00:00'],
    ['Alemania',            'Paraguay',            '2026-06-29 14:30:00'],
    ['Países Bajos',        'Marruecos',           '2026-06-29 19:00:00'],
    ['Costa de Marfil',     'Noruega',             '2026-06-30 11:00:00'],
    ['Francia',             'Suecia',              '2026-06-30 15:00:00'],
    ['México',              'Ecuador',             '2026-06-30 19:00:00'],
    ['Inglaterra',          'RD Congo',            '2026-07-01 10:00:00'],
    ['Bélgica',             'Senegal',             '2026-07-01 14:00:00'],
    ['Estados Unidos',      'Bosnia y Herzegovina','2026-07-01 18:00:00'],
    ['España',              'Austria',             '2026-07-02 13:00:00'],
    ['Portugal',            'Croacia',             '2026-07-02 17:00:00'],
    ['Suiza',               'Argelia',             '2026-07-02 21:00:00'],
    ['Australia',           'Egipto',              '2026-07-03 12:00:00'],
    ['Argentina',           'Cabo Verde',          '2026-07-03 16:00:00'],
    ['Colombia',            'Ghana',               '2026-07-03 19:30:00'],
];

foreach ($r32 as $g) {
    Game::create([
        'home_team_id' => $T[$g[0]],
        'away_team_id' => $T[$g[1]],
        'match_date'   => \Carbon\Carbon::parse($g[2]),
        'stage'        => 'r32',
        'status'       => 'pending',
    ]);
    echo "✅ {$g[0]} vs {$g[1]}\n";
}

echo "\n=== PUNTOS DESPUÉS (deben ser iguales) ===\n";
User::orderBy('points', 'desc')->get(['name','points'])->each(fn($u) => print("{$u->name}: {$u->points} pts\n"));

echo "\nTotal partidos en BD: " . Game::count() . "\n";
echo "R32: " . Game::where('stage','r32')->count() . " | Grupos: " . Game::where('stage','group')->count() . "\n";
