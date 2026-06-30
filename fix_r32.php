<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;
use Carbon\Carbon;

// Mapeo: nombre => id real
$T = [
    'Sudáfrica'           => 2,
    'Canadá'              => 5,
    'Brasil'              => 9,
    'Japón'               => 21,
    'Alemania'            => 17,
    'Paraguay'            => 14,
    'Países Bajos'        => 22,
    'Marruecos'           => 10,
    'Costa de Marfil'     => 19,
    'Noruega'             => 36,
    'Francia'             => 33,
    'Suecia'              => 23,
    'México'              => 1,
    'Ecuador'             => 20,
    'Inglaterra'          => 45,
    'RD Congo'            => 42,
    'Bélgica'             => 25,
    'Senegal'             => 34,
    'Estados Unidos'      => 13,
    'Bosnia y Herzegovina'=> 6,
    'España'              => 29,
    'Austria'             => 39,
    'Portugal'            => 43,
    'Croacia'             => 46,
    'Suiza'               => 8,
    'Argelia'             => 38,
    'Australia'           => 15,
    'Egipto'              => 26,
    'Argentina'           => 37,
    'Cabo Verde'          => 30,
    'Colombia'            => 41,
    'Ghana'               => 47,
];

// Horario oficial R32 — hora Guatemala
$r32 = [
    // Dom 28 jun
    ['Sudáfrica',           'Canadá',              '2026-06-28 13:00:00'],
    // Lun 29 jun
    ['Brasil',              'Japón',               '2026-06-29 11:00:00'],
    ['Alemania',            'Paraguay',            '2026-06-29 14:30:00'],
    ['Países Bajos',        'Marruecos',           '2026-06-29 19:00:00'],
    // Mar 30 jun
    ['Costa de Marfil',     'Noruega',             '2026-06-30 11:00:00'],
    ['Francia',             'Suecia',              '2026-06-30 15:00:00'],
    ['México',              'Ecuador',             '2026-06-30 19:00:00'],
    // Mié 1 jul
    ['Inglaterra',          'RD Congo',            '2026-07-01 10:00:00'],
    ['Bélgica',             'Senegal',             '2026-07-01 14:00:00'],
    ['Estados Unidos',      'Bosnia y Herzegovina','2026-07-01 18:00:00'],
    // Jue 2 jul
    ['España',              'Austria',             '2026-07-02 13:00:00'],
    ['Portugal',            'Croacia',             '2026-07-02 17:00:00'],
    ['Suiza',               'Argelia',             '2026-07-02 21:00:00'],
    // Vie 3 jul
    ['Australia',           'Egipto',              '2026-07-03 12:00:00'],
    ['Argentina',           'Cabo Verde',          '2026-07-03 16:00:00'],
    ['Colombia',            'Ghana',               '2026-07-03 19:30:00'],
];

// Borrar partidos R32 actuales
$deleted = Game::where('stage', 'r32')->delete();
echo "Borrados: $deleted partidos R32\n";

// Insertar los nuevos
foreach ($r32 as $g) {
    Game::create([
        'home_team_id' => $T[$g[0]],
        'away_team_id' => $T[$g[1]],
        'match_date'   => Carbon::parse($g[2]),
        'stage'        => 'r32',
        'status'       => 'pending',
    ]);
    echo "✅ {$g[0]} vs {$g[1]} — {$g[2]}\n";
}

echo "\nTotal R32 ahora: " . Game::where('stage','r32')->count() . "\n";
