<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Game;
use App\Models\User;
use App\Models\Prediction;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class WorldCupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin and Test Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@quiniela.com'],
            [
                'name' => 'Admin Quiniela',
                'password' => Hash::make('admin123.hugo'),
                'role' => 'admin',
                'points' => 0
            ]
        );


        // 2. Teams — 48 real teams (Groups A–L)
        $teamsData = [
            // Group A
            ['name' => 'México', 'code' => 'mx', 'group' => 'A'],
            ['name' => 'Sudáfrica', 'code' => 'za', 'group' => 'A'],
            ['name' => 'Corea del Sur', 'code' => 'kr', 'group' => 'A'],
            ['name' => 'Chequia', 'code' => 'cz', 'group' => 'A'],
            // Group B
            ['name' => 'Canadá', 'code' => 'ca', 'group' => 'B'],
            ['name' => 'Bosnia y Herzegovina', 'code' => 'ba', 'group' => 'B'],
            ['name' => 'Catar', 'code' => 'qa', 'group' => 'B'],
            ['name' => 'Suiza', 'code' => 'ch', 'group' => 'B'],
            // Group C
            ['name' => 'Brasil', 'code' => 'br', 'group' => 'C'],
            ['name' => 'Marruecos', 'code' => 'ma', 'group' => 'C'],
            ['name' => 'Haití', 'code' => 'ht', 'group' => 'C'],
            ['name' => 'Escocia', 'code' => 'gb-sct', 'group' => 'C'],
            // Group D
            ['name' => 'Estados Unidos', 'code' => 'us', 'group' => 'D'],
            ['name' => 'Paraguay', 'code' => 'py', 'group' => 'D'],
            ['name' => 'Australia', 'code' => 'au', 'group' => 'D'],
            ['name' => 'Turquía', 'code' => 'tr', 'group' => 'D'],
            // Group E
            ['name' => 'Alemania', 'code' => 'de', 'group' => 'E'],
            ['name' => 'Curazao', 'code' => 'cw', 'group' => 'E'],
            ['name' => 'Costa de Marfil', 'code' => 'ci', 'group' => 'E'],
            ['name' => 'Ecuador', 'code' => 'ec', 'group' => 'E'],
            // Group F
            ['name' => 'Japón', 'code' => 'jp', 'group' => 'F'],
            ['name' => 'Países Bajos', 'code' => 'nl', 'group' => 'F'],
            ['name' => 'Suecia', 'code' => 'se', 'group' => 'F'],
            ['name' => 'Túnez', 'code' => 'tn', 'group' => 'F'],
            // Group G
            ['name' => 'Bélgica', 'code' => 'be', 'group' => 'G'],
            ['name' => 'Egipto', 'code' => 'eg', 'group' => 'G'],
            ['name' => 'Irán', 'code' => 'ir', 'group' => 'G'],
            ['name' => 'Nueva Zelanda', 'code' => 'nz', 'group' => 'G'],
            // Group H
            ['name' => 'España', 'code' => 'es', 'group' => 'H'],
            ['name' => 'Cabo Verde', 'code' => 'cv', 'group' => 'H'],
            ['name' => 'Arabia Saudita', 'code' => 'sa', 'group' => 'H'],
            ['name' => 'Uruguay', 'code' => 'uy', 'group' => 'H'],
            // Group I
            ['name' => 'Francia', 'code' => 'fr', 'group' => 'I'],
            ['name' => 'Senegal', 'code' => 'sn', 'group' => 'I'],
            ['name' => 'Irak', 'code' => 'iq', 'group' => 'I'],
            ['name' => 'Noruega', 'code' => 'no', 'group' => 'I'],
            // Group J
            ['name' => 'Argentina', 'code' => 'ar', 'group' => 'J'],
            ['name' => 'Argelia', 'code' => 'dz', 'group' => 'J'],
            ['name' => 'Austria', 'code' => 'at', 'group' => 'J'],
            ['name' => 'Jordania', 'code' => 'jo', 'group' => 'J'],
            // Group K
            ['name' => 'Colombia', 'code' => 'co', 'group' => 'K'],
            ['name' => 'RD Congo', 'code' => 'cd', 'group' => 'K'],
            ['name' => 'Portugal', 'code' => 'pt', 'group' => 'K'],
            ['name' => 'Uzbekistán', 'code' => 'uz', 'group' => 'K'],
            // Group L
            ['name' => 'Inglaterra', 'code' => 'gb-eng', 'group' => 'L'],
            ['name' => 'Croacia', 'code' => 'hr', 'group' => 'L'],
            ['name' => 'Ghana', 'code' => 'gh', 'group' => 'L'],
            ['name' => 'Panamá', 'code' => 'pa', 'group' => 'L'],
        ];

        $T = []; // team models indexed by name
        foreach ($teamsData as $team) {
            $T[$team['name']] = Team::updateOrCreate(
                ['code' => $team['code']],
                ['name' => $team['name'], 'group' => $team['group']]
            );
        }

        // 3. Placeholder teams for knockout rounds
        // R32: 32 bracket position teams (24 group qualifiers + 8 best 3rd-place)
        $r32Names = [
            ['name' => '1° Grupo A', 'code' => 'p-1a'],
            ['name' => '2° Grupo A', 'code' => 'p-2a'],
            ['name' => '1° Grupo B', 'code' => 'p-1b'],
            ['name' => '2° Grupo B', 'code' => 'p-2b'],
            ['name' => '1° Grupo C', 'code' => 'p-1c'],
            ['name' => '2° Grupo C', 'code' => 'p-2c'],
            ['name' => '1° Grupo D', 'code' => 'p-1d'],
            ['name' => '2° Grupo D', 'code' => 'p-2d'],
            ['name' => '1° Grupo E', 'code' => 'p-1e'],
            ['name' => '2° Grupo E', 'code' => 'p-2e'],
            ['name' => '1° Grupo F', 'code' => 'p-1f'],
            ['name' => '2° Grupo F', 'code' => 'p-2f'],
            ['name' => '1° Grupo G', 'code' => 'p-1g'],
            ['name' => '2° Grupo G', 'code' => 'p-2g'],
            ['name' => '1° Grupo H', 'code' => 'p-1h'],
            ['name' => '2° Grupo H', 'code' => 'p-2h'],
            ['name' => '1° Grupo I', 'code' => 'p-1i'],
            ['name' => '2° Grupo I', 'code' => 'p-2i'],
            ['name' => '1° Grupo J', 'code' => 'p-1j'],
            ['name' => '2° Grupo J', 'code' => 'p-2j'],
            ['name' => '1° Grupo K', 'code' => 'p-1k'],
            ['name' => '2° Grupo K', 'code' => 'p-2k'],
            ['name' => '1° Grupo L', 'code' => 'p-1l'],
            ['name' => '2° Grupo L', 'code' => 'p-2l'],
            ['name' => 'Mejor 3° (1)', 'code' => 'p-3rd-1'],
            ['name' => 'Mejor 3° (2)', 'code' => 'p-3rd-2'],
            ['name' => 'Mejor 3° (3)', 'code' => 'p-3rd-3'],
            ['name' => 'Mejor 3° (4)', 'code' => 'p-3rd-4'],
            ['name' => 'Mejor 3° (5)', 'code' => 'p-3rd-5'],
            ['name' => 'Mejor 3° (6)', 'code' => 'p-3rd-6'],
            ['name' => 'Mejor 3° (7)', 'code' => 'p-3rd-7'],
            ['name' => 'Mejor 3° (8)', 'code' => 'p-3rd-8'],
        ];
        $r32Teams = [];
        foreach ($r32Names as $t) {
            $r32Teams[] = Team::updateOrCreate(['code' => $t['code']], ['name' => $t['name'], 'group' => 'TBD']);
        }

        // R16: 16 teams (winners of R32 games)
        $r16Teams = [];
        for ($i = 1; $i <= 16; $i++) {
            $r16Teams[] = Team::updateOrCreate(
                ['code' => 'wr32-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                ['name' => 'Gan. R32 #' . $i, 'group' => 'TBD']
            );
        }

        // QF: 8 teams (winners of R16 games)
        $qfTeams = [];
        for ($i = 1; $i <= 8; $i++) {
            $qfTeams[] = Team::updateOrCreate(
                ['code' => 'wr16-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                ['name' => 'Gan. R16 #' . $i, 'group' => 'TBD']
            );
        }

        // SF: 4 teams (winners of QF games)
        $sfTeams = [];
        for ($i = 1; $i <= 4; $i++) {
            $sfTeams[] = Team::updateOrCreate(
                ['code' => 'wqf-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                ['name' => 'Gan. Cuartos #' . $i, 'group' => 'TBD']
            );
        }

        // Final and 3rd place: 4 teams (2 SF winners → Final, 2 SF losers → 3rd Place)
        $finalHome = Team::updateOrCreate(['code' => 'wsf-1'], ['name' => 'Gan. Semifinal #1', 'group' => 'TBD']);
        $finalAway = Team::updateOrCreate(['code' => 'wsf-2'], ['name' => 'Gan. Semifinal #2', 'group' => 'TBD']);
        $thirdHome = Team::updateOrCreate(['code' => 'lsf-1'], ['name' => 'Per. Semifinal #1', 'group' => 'TBD']);
        $thirdAway = Team::updateOrCreate(['code' => 'lsf-2'], ['name' => 'Per. Semifinal #2', 'group' => 'TBD']);

        // =====================================================================
        // 4. Group Stage Games (72 total — 6 per group × 12 groups)
        // =====================================================================
        // Format: [home, away, date, stage]
        // Last 2 games of each group (MD3) played simultaneously
        // Horarios en hora de Guatemala (UTC-6) — fuente oficial
        $groupGames = [
            // ── GROUP A ──  (Jue 11 jun | Jue 18 jun | Mié 24 jun)
            ['México', 'Sudáfrica', '2026-06-11 13:00:00', 'group'],
            ['Corea del Sur', 'Chequia', '2026-06-11 20:00:00', 'group'],
            ['Chequia', 'Sudáfrica', '2026-06-18 10:00:00', 'group'],
            ['México', 'Corea del Sur', '2026-06-18 19:00:00', 'group'],
            ['Sudáfrica', 'Corea del Sur', '2026-06-24 19:00:00', 'group'],
            ['Chequia', 'México', '2026-06-24 19:00:00', 'group'],
            // ── GROUP B ──  (Vie 12 jun | Jue 18 jun | Mié 24 jun)
            ['Canadá', 'Bosnia y Herzegovina', '2026-06-12 13:00:00', 'group'],
            ['Catar', 'Suiza', '2026-06-13 13:00:00', 'group'],
            ['Suiza', 'Bosnia y Herzegovina', '2026-06-18 13:00:00', 'group'],
            ['Canadá', 'Catar', '2026-06-18 16:00:00', 'group'],
            ['Suiza', 'Canadá', '2026-06-24 13:00:00', 'group'],
            ['Bosnia y Herzegovina', 'Catar', '2026-06-24 13:00:00', 'group'],
            // ── GROUP C ──  (Sáb 13 jun | Vie 19 jun | Mié 24 jun)
            ['Brasil', 'Marruecos', '2026-06-13 16:00:00', 'group'],
            ['Haití', 'Escocia', '2026-06-13 19:00:00', 'group'],
            ['Escocia', 'Marruecos', '2026-06-19 16:00:00', 'group'],
            ['Brasil', 'Haití', '2026-06-19 18:30:00', 'group'],
            ['Marruecos', 'Haití', '2026-06-24 16:00:00', 'group'],
            ['Escocia', 'Brasil', '2026-06-24 16:00:00', 'group'],
            // ── GROUP D ──  (Vie 12 jun | Vie 19 jun | Jue 25 jun)
            ['Estados Unidos', 'Paraguay', '2026-06-12 19:00:00', 'group'],
            ['Australia', 'Turquía', '2026-06-13 22:00:00', 'group'],
            ['Estados Unidos', 'Australia', '2026-06-19 13:00:00', 'group'],
            ['Turquía', 'Paraguay', '2026-06-19 21:00:00', 'group'],
            ['Turquía', 'Estados Unidos', '2026-06-25 20:00:00', 'group'],
            ['Paraguay', 'Australia', '2026-06-25 20:00:00', 'group'],
            // ── GROUP E ──  (Dom 14 jun | Sáb 20 jun | Jue 25 jun)
            ['Alemania', 'Curazao', '2026-06-14 11:00:00', 'group'],
            ['Costa de Marfil', 'Ecuador', '2026-06-14 17:00:00', 'group'],
            ['Alemania', 'Costa de Marfil', '2026-06-20 14:00:00', 'group'],
            ['Ecuador', 'Curazao', '2026-06-20 18:00:00', 'group'],
            ['Curazao', 'Costa de Marfil', '2026-06-25 14:00:00', 'group'],
            ['Ecuador', 'Alemania', '2026-06-25 14:00:00', 'group'],
            // ── GROUP F ──  (Dom 14 jun | Sáb 20 jun | Jue 25 jun)
            ['Países Bajos', 'Japón', '2026-06-14 14:00:00', 'group'],
            ['Suecia', 'Túnez', '2026-06-14 20:00:00', 'group'],
            ['Países Bajos', 'Suecia', '2026-06-20 11:00:00', 'group'],
            ['Túnez', 'Japón', '2026-06-20 22:00:00', 'group'],
            ['Túnez', 'Países Bajos', '2026-06-25 17:00:00', 'group'],
            ['Japón', 'Suecia', '2026-06-25 17:00:00', 'group'],
            // ── GROUP G ──  (Lun 15 jun | Dom 21 jun | Vie 26 jun)
            ['Bélgica', 'Egipto', '2026-06-15 13:00:00', 'group'],
            ['Irán', 'Nueva Zelanda', '2026-06-15 19:00:00', 'group'],
            ['Bélgica', 'Irán', '2026-06-21 13:00:00', 'group'],
            ['Nueva Zelanda', 'Egipto', '2026-06-21 19:00:00', 'group'],
            ['Nueva Zelanda', 'Bélgica', '2026-06-26 21:00:00', 'group'],
            ['Egipto', 'Irán', '2026-06-26 21:00:00', 'group'],
            // ── GROUP H ──  (Lun 15 jun | Dom 21 jun | Vie 26 jun)
            ['España', 'Cabo Verde', '2026-06-15 10:00:00', 'group'],
            ['Arabia Saudita', 'Uruguay', '2026-06-15 16:00:00', 'group'],
            ['España', 'Arabia Saudita', '2026-06-21 10:00:00', 'group'],
            ['Uruguay', 'Cabo Verde', '2026-06-21 16:00:00', 'group'],
            ['Cabo Verde', 'Arabia Saudita', '2026-06-26 18:00:00', 'group'],
            ['Uruguay', 'España', '2026-06-26 18:00:00', 'group'],
            // ── GROUP I ──  (Mar 16 jun | Lun 22 jun | Vie 26 jun)
            ['Francia', 'Senegal', '2026-06-16 13:00:00', 'group'],
            ['Irak', 'Noruega', '2026-06-16 16:00:00', 'group'],
            ['Francia', 'Irak', '2026-06-22 15:00:00', 'group'],
            ['Noruega', 'Senegal', '2026-06-22 18:00:00', 'group'],
            ['Noruega', 'Francia', '2026-06-26 13:00:00', 'group'],
            ['Senegal', 'Irak', '2026-06-26 13:00:00', 'group'],
            // ── GROUP J ──  (Mar 16 jun | Lun 22 jun | Sáb 27 jun)
            ['Argentina', 'Argelia', '2026-06-16 19:00:00', 'group'],
            ['Austria', 'Jordania', '2026-06-16 22:00:00', 'group'],
            ['Argentina', 'Austria', '2026-06-22 11:00:00', 'group'],
            ['Jordania', 'Argelia', '2026-06-22 21:00:00', 'group'],
            ['Argelia', 'Austria', '2026-06-27 20:00:00', 'group'],
            ['Jordania', 'Argentina', '2026-06-27 20:00:00', 'group'],
            // ── GROUP K ──  (Mié 17 jun | Mar 23 jun | Sáb 27 jun)
            ['Portugal', 'RD Congo', '2026-06-17 11:00:00', 'group'],
            ['Uzbekistán', 'Colombia', '2026-06-17 20:00:00', 'group'],
            ['Portugal', 'Uzbekistán', '2026-06-23 11:00:00', 'group'],
            ['Colombia', 'RD Congo', '2026-06-23 20:00:00', 'group'],
            ['Colombia', 'Portugal', '2026-06-27 17:30:00', 'group'],
            ['RD Congo', 'Uzbekistán', '2026-06-27 17:30:00', 'group'],
            // ── GROUP L ──  (Mié 17 jun | Mar 23 jun | Sáb 27 jun)
            ['Inglaterra', 'Croacia', '2026-06-17 14:00:00', 'group'],
            ['Ghana', 'Panamá', '2026-06-17 17:00:00', 'group'],
            ['Inglaterra', 'Ghana', '2026-06-23 14:00:00', 'group'],
            ['Panamá', 'Croacia', '2026-06-23 17:00:00', 'group'],
            ['Panamá', 'Inglaterra', '2026-06-27 15:00:00', 'group'],
            ['Croacia', 'Ghana', '2026-06-27 15:00:00', 'group'],
        ];

        foreach ($groupGames as $g) {
            $homeTeamId = $T[$g[0]]->id;
            $awayTeamId = $T[$g[1]]->id;
            $matchDate  = Carbon::parse($g[2]);

            $exists = Game::where('home_team_id', $homeTeamId)
                          ->where('away_team_id', $awayTeamId)
                          ->where('match_date', $matchDate)
                          ->exists();

            if (!$exists) {
                Game::create([
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                    'match_date'   => $matchDate,
                    'stage'        => $g[3],
                    'status'       => 'pending',
                ]);
            }
        }

        // =====================================================================
        // 5. Knockout Stage Games (32 total)
        // =====================================================================

        // — Dieciseisavos (R32): 16 partidos — hora Guatemala — fuente oficial FIFA 2026
        // Los partidos del R32 siguen el bracket oficial:
        // Dom 28 jun: Sudáfrica vs Canadá (13:00)
        // Lun 29 jun: Brasil vs Japón (11:00), Alemania vs Paraguay (14:30), Países Bajos vs Marruecos (19:00)
        // Mar 30 jun: Costa de Marfil vs Noruega (11:00), Francia vs Suecia (15:00), México vs Ecuador (19:00)
        // Mié 1 jul: Inglaterra vs RD Congo (10:00), Bélgica vs Senegal (14:00), EE.UU. vs Bosnia (18:00)
        // Jue 2 jul: España vs Austria (13:00), Portugal vs Croacia (17:00), Suiza vs Argelia (21:00)
        // Vie 3 jul: Australia vs Egipto (12:00), Argentina vs Cabo Verde (16:00), Colombia vs Ghana (19:30)
        $r32GameData = [
            // Dom 28 jun
            [$r32Teams[0],  $r32Teams[3],  '2026-06-28 13:00:00'],  // Sudáfrica vs Canadá
            // Lun 29 jun
            [$r32Teams[4],  $r32Teams[7],  '2026-06-29 11:00:00'],  // Brasil vs Japón
            [$r32Teams[8],  $r32Teams[11], '2026-06-29 14:30:00'],  // Alemania vs Paraguay
            [$r32Teams[12], $r32Teams[15], '2026-06-29 19:00:00'],  // Países Bajos vs Marruecos
            // Mar 30 jun
            [$r32Teams[16], $r32Teams[19], '2026-06-30 11:00:00'],  // Costa de Marfil vs Noruega
            [$r32Teams[20], $r32Teams[23], '2026-06-30 15:00:00'],  // Francia vs Suecia
            [$r32Teams[1],  $r32Teams[2],  '2026-06-30 19:00:00'],  // México vs Ecuador
            // Mié 1 jul
            [$r32Teams[5],  $r32Teams[6],  '2026-07-01 10:00:00'],  // Inglaterra vs RD Congo
            [$r32Teams[9],  $r32Teams[10], '2026-07-01 14:00:00'],  // Bélgica vs Senegal
            [$r32Teams[13], $r32Teams[14], '2026-07-01 18:00:00'],  // EE.UU. vs Bosnia y Herzegovina
            // Jue 2 jul
            [$r32Teams[17], $r32Teams[18], '2026-07-02 13:00:00'],  // España vs Austria
            [$r32Teams[21], $r32Teams[22], '2026-07-02 17:00:00'],  // Portugal vs Croacia
            [$r32Teams[24], $r32Teams[25], '2026-07-02 21:00:00'],  // Suiza vs Argelia
            // Vie 3 jul
            [$r32Teams[26], $r32Teams[27], '2026-07-03 12:00:00'],  // Australia vs Egipto
            [$r32Teams[28], $r32Teams[29], '2026-07-03 16:00:00'],  // Argentina vs Cabo Verde
            [$r32Teams[30], $r32Teams[31], '2026-07-03 19:30:00'],  // Colombia vs Ghana
        ];
        foreach ($r32GameData as $g) {
            $exists = Game::where('home_team_id', $g[0]->id)
                          ->where('away_team_id', $g[1]->id)
                          ->exists();
            if (!$exists) {
                Game::create([
                    'home_team_id' => $g[0]->id,
                    'away_team_id' => $g[1]->id,
                    'match_date'   => Carbon::parse($g[2]),
                    'stage'        => 'r32',
                    'status'       => 'pending',
                ]);
            }
        }

        // — Octavos de final (R16): 8 partidos — 4 al 7 jul — fuente oficial hora Guatemala
        $r16GameData = [
            // Sáb 4 jul
            [$r16Teams[0], $r16Teams[1], '2026-07-04 11:00:00'],  // Llave 2: G73 vs G75
            [$r16Teams[2], $r16Teams[3], '2026-07-04 15:00:00'],  // Llave 1: G74 vs G77
            // Dom 5 jul
            [$r16Teams[4], $r16Teams[5], '2026-07-05 14:00:00'],  // Llave 5: G76 vs G78
            [$r16Teams[6], $r16Teams[7], '2026-07-05 18:00:00'],  // Llave 6: G79 vs G80
            // Lun 6 jul
            [$r16Teams[8], $r16Teams[9], '2026-07-06 13:00:00'],  // Llave 3: G83 vs G84
            [$r16Teams[10], $r16Teams[11], '2026-07-06 18:00:00'],  // Llave 4: G81 vs G82
            // Mar 7 jul
            [$r16Teams[12], $r16Teams[13], '2026-07-07 10:00:00'],  // Llave 7: G86 vs G88
            [$r16Teams[14], $r16Teams[15], '2026-07-07 14:00:00'],  // Llave 8: G85 vs G87
        ];
        foreach ($r16GameData as $g) {
            $exists = Game::where('home_team_id', $g[0]->id)
                          ->where('away_team_id', $g[1]->id)
                          ->where('match_date', Carbon::parse($g[2]))
                          ->exists();
            if (!$exists) {
                Game::create([
                    'home_team_id' => $g[0]->id,
                    'away_team_id' => $g[1]->id,
                    'match_date'   => Carbon::parse($g[2]),
                    'stage'        => 'r16',
                    'status'       => 'pending',
                ]);
            }
        }

        // — Cuartos de final (QF): 4 partidos — 9 al 11 jul — fuente oficial hora Guatemala
        $qfGameData = [
            [$qfTeams[0], $qfTeams[1], '2026-07-09 14:00:00'],   // Jue 9 jul
            [$qfTeams[2], $qfTeams[3], '2026-07-10 13:00:00'],   // Vie 10 jul
            [$qfTeams[4], $qfTeams[5], '2026-07-11 15:00:00'],   // Sáb 11 jul
            [$qfTeams[6], $qfTeams[7], '2026-07-11 19:00:00'],   // Sáb 11 jul
        ];
        foreach ($qfGameData as $g) {
            $exists = Game::where('home_team_id', $g[0]->id)
                          ->where('away_team_id', $g[1]->id)
                          ->where('match_date', Carbon::parse($g[2]))
                          ->exists();
            if (!$exists) {
                Game::create([
                    'home_team_id' => $g[0]->id,
                    'away_team_id' => $g[1]->id,
                    'match_date'   => Carbon::parse($g[2]),
                    'stage'        => 'quarter',
                    'status'       => 'pending',
                ]);
            }
        }

        // — Semifinales (SF): 2 partidos — 14 y 15 jul — fuente oficial hora Guatemala
        $sfGameData = [
            [$sfTeams[0], $sfTeams[1], '2026-07-14 13:00:00'],  // Mar 14 jul 13:00
            [$sfTeams[2], $sfTeams[3], '2026-07-15 13:00:00'],  // Mié 15 jul 13:00
        ];
        foreach ($sfGameData as $g) {
            $exists = Game::where('home_team_id', $g[0]->id)
                          ->where('away_team_id', $g[1]->id)
                          ->where('match_date', Carbon::parse($g[2]))
                          ->exists();
            if (!$exists) {
                Game::create([
                    'home_team_id' => $g[0]->id,
                    'away_team_id' => $g[1]->id,
                    'match_date'   => Carbon::parse($g[2]),
                    'stage'        => 'semi',
                    'status'       => 'pending',
                ]);
            }
        }

        // — Tercer Lugar: 1 partido — Sáb 18 jul 15:00 GT
        $thirdExists = Game::where('home_team_id', $thirdHome->id)
                           ->where('away_team_id', $thirdAway->id)
                           ->where('match_date', Carbon::parse('2026-07-18 15:00:00'))
                           ->exists();
        if (!$thirdExists) {
            Game::create([
                'home_team_id' => $thirdHome->id,
                'away_team_id' => $thirdAway->id,
                'match_date'   => Carbon::parse('2026-07-18 15:00:00'),
                'stage'        => 'third_place',
                'status'       => 'pending',
            ]);
        }

        // — Final: 1 partido — Dom 19 jul 13:00 GT
        $finalExists = Game::where('home_team_id', $finalHome->id)
                           ->where('away_team_id', $finalAway->id)
                           ->where('match_date', Carbon::parse('2026-07-19 13:00:00'))
                           ->exists();
        if (!$finalExists) {
            Game::create([
                'home_team_id' => $finalHome->id,
                'away_team_id' => $finalAway->id,
                'match_date'   => Carbon::parse('2026-07-19 13:00:00'),
                'stage'        => 'final',
                'status'       => 'pending',
            ]);
        }

        // 6. Initialize champion setting (only if not already set)
        if (!Setting::getValue('world_cup_champion')) {
            Setting::setValue('world_cup_champion', null);
        }
    }
}
