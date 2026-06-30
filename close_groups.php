<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Game;

// Contar pendientes
$pending = Game::where('stage', 'group')->where('status', 'pending')->count();
echo "Partidos de grupo pendientes: $pending\n";

// Marcar todos como terminados (los que no tengan marcador quedan 0-0)
$updated = Game::where('stage', 'group')
    ->where('status', 'pending')
    ->update([
        'status'     => 'finished',
        'home_score' => \Illuminate\Support\Facades\DB::raw('COALESCE(home_score, 0)'),
        'away_score' => \Illuminate\Support\Facades\DB::raw('COALESCE(away_score, 0)'),
    ]);

echo "Actualizados: $updated partidos\n";
echo "Fase de grupos: " . Game::where('stage', 'group')->where('status', 'finished')->count() . " finalizados\n";
echo "Pendientes restantes: " . Game::where('stage', 'group')->where('status', 'pending')->count() . "\n";
echo "\nListo. Ahora los 16avos de final estarán desbloqueados.\n";
