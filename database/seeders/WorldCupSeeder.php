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
        $groupGames = [
            // ── GROUP A ──
            ['México', 'Sudáfrica', '2026-06-11 17:00:00', 'group'],
            ['Corea del Sur', 'Chequia', '2026-06-11 20:00:00', 'group'],
            ['México', 'Corea del Sur', '2026-06-17 17:00:00', 'group'],
            ['Sudáfrica', 'Chequia', '2026-06-17 20:00:00', 'group'],
            ['México', 'Chequia', '2026-06-23 18:00:00', 'group'],
            ['Sudáfrica', 'Corea del Sur', '2026-06-23 18:00:00', 'group'],
            // ── GROUP B ──
            ['Canadá', 'Bosnia y Herzegovina', '2026-06-12 17:00:00', 'group'],
            ['Catar', 'Suiza', '2026-06-12 20:00:00', 'group'],
            ['Canadá', 'Catar', '2026-06-18 17:00:00', 'group'],
            ['Bosnia y Herzegovina', 'Suiza', '2026-06-18 20:00:00', 'group'],
            ['Canadá', 'Suiza', '2026-06-24 18:00:00', 'group'],
            ['Bosnia y Herzegovina', 'Catar', '2026-06-24 18:00:00', 'group'],
            // ── GROUP C ──
            ['Brasil', 'Escocia', '2026-06-13 14:00:00', 'group'],
            ['Marruecos', 'Haití', '2026-06-13 20:00:00', 'group'],
            ['Brasil', 'Marruecos', '2026-06-19 14:00:00', 'group'],
            ['Haití', 'Escocia', '2026-06-19 20:00:00', 'group'],
            ['Brasil', 'Haití', '2026-06-25 18:00:00', 'group'],
            ['Marruecos', 'Escocia', '2026-06-25 18:00:00', 'group'],
            // ── GROUP D ──
            ['Estados Unidos', 'Paraguay', '2026-06-12 20:00:00', 'group'],
            ['Australia', 'Turquía', '2026-06-13 17:00:00', 'group'],
            ['Estados Unidos', 'Australia', '2026-06-19 17:00:00', 'group'],
            ['Paraguay', 'Turquía', '2026-06-19 23:00:00', 'group'],
            ['Estados Unidos', 'Turquía', '2026-06-26 18:00:00', 'group'],
            ['Paraguay', 'Australia', '2026-06-26 18:00:00', 'group'],
            // ── GROUP E ──
            ['Alemania', 'Ecuador', '2026-06-13 17:00:00', 'group'],
            ['Curazao', 'Costa de Marfil', '2026-06-14 14:00:00', 'group'],
            ['Alemania', 'Curazao', '2026-06-20 17:00:00', 'group'],
            ['Costa de Marfil', 'Ecuador', '2026-06-20 20:00:00', 'group'],
            ['Alemania', 'Costa de Marfil', '2026-06-26 21:00:00', 'group'],
            ['Curazao', 'Ecuador', '2026-06-26 21:00:00', 'group'],
            // ── GROUP F ──
            ['Países Bajos', 'Japón', '2026-06-14 17:00:00', 'group'],
            ['Suecia', 'Túnez', '2026-06-15 14:00:00', 'group'],
            ['Japón', 'Suecia', '2026-06-21 14:00:00', 'group'],
            ['Países Bajos', 'Túnez', '2026-06-21 17:00:00', 'group'],
            ['Japón', 'Túnez', '2026-06-27 18:00:00', 'group'],
            ['Países Bajos', 'Suecia', '2026-06-27 18:00:00', 'group'],
            // ── GROUP G ──
            ['Bélgica', 'Egipto', '2026-06-14 20:00:00', 'group'],
            ['Irán', 'Nueva Zelanda', '2026-06-15 17:00:00', 'group'],
            ['Bélgica', 'Irán', '2026-06-21 20:00:00', 'group'],
            ['Egipto', 'Nueva Zelanda', '2026-06-21 23:00:00', 'group'],
            ['Bélgica', 'Nueva Zelanda', '2026-06-28 18:00:00', 'group'],
            ['Egipto', 'Irán', '2026-06-28 18:00:00', 'group'],
            // ── GROUP H ──
            ['España', 'Uruguay', '2026-06-14 14:00:00', 'group'],
            ['Cabo Verde', 'Arabia Saudita', '2026-06-15 20:00:00', 'group'],
            ['España', 'Cabo Verde', '2026-06-22 14:00:00', 'group'],
            ['Arabia Saudita', 'Uruguay', '2026-06-22 17:00:00', 'group'],
            ['España', 'Arabia Saudita', '2026-06-29 18:00:00', 'group'],
            ['Cabo Verde', 'Uruguay', '2026-06-29 18:00:00', 'group'],
            // ── GROUP I ──
            ['Francia', 'Noruega', '2026-06-14 17:00:00', 'group'],
            ['Senegal', 'Irak', '2026-06-16 14:00:00', 'group'],
            ['Francia', 'Senegal', '2026-06-22 20:00:00', 'group'],
            ['Irak', 'Noruega', '2026-06-22 23:00:00', 'group'],
            ['Francia', 'Irak', '2026-06-29 21:00:00', 'group'],
            ['Senegal', 'Noruega', '2026-06-29 21:00:00', 'group'],
            // ── GROUP J ──
            ['Argentina', 'Austria', '2026-06-15 15:00:00', 'group'],
            ['Argelia', 'Jordania', '2026-06-16 17:00:00', 'group'],
            ['Argentina', 'Argelia', '2026-06-23 17:00:00', 'group'],
            ['Austria', 'Jordania', '2026-06-23 20:00:00', 'group'],
            ['Argentina', 'Jordania', '2026-06-30 18:00:00', 'group'],
            ['Argelia', 'Austria', '2026-06-30 18:00:00', 'group'],
            // ── GROUP K ──
            ['Colombia', 'RD Congo', '2026-06-16 20:00:00', 'group'],
            ['Portugal', 'Uzbekistán', '2026-06-17 14:00:00', 'group'],
            ['Colombia', 'Portugal', '2026-06-24 17:00:00', 'group'],
            ['RD Congo', 'Uzbekistán', '2026-06-24 20:00:00', 'group'],
            ['Colombia', 'Uzbekistán', '2026-07-01 18:00:00', 'group'],
            ['RD Congo', 'Portugal', '2026-07-01 18:00:00', 'group'],
            // ── GROUP L ──
            ['Inglaterra', 'Croacia', '2026-06-15 18:00:00', 'group'],
            ['Ghana', 'Panamá', '2026-06-16 23:00:00', 'group'],
            ['Inglaterra', 'Ghana', '2026-06-23 14:00:00', 'group'],
            ['Croacia', 'Panamá', '2026-06-24 23:00:00', 'group'],
            ['Inglaterra', 'Panamá', '2026-07-01 21:00:00', 'group'],
            ['Croacia', 'Ghana', '2026-07-01 21:00:00', 'group'],
        ];

        foreach ($groupGames as $g) {
            Game::updateOrCreate(
                [
                    'home_team_id' => $T[$g[0]]->id,
                    'away_team_id' => $T[$g[1]]->id,
                    'match_date' => Carbon::parse($g[2]),
                ],
                ['stage' => $g[3], 'status' => 'pending']
            );
        }

        // =====================================================================
        // 5. Knockout Stage Games (32 total)
        // =====================================================================

        // — Round of 32 (R32): 16 games — Jul 4–11
        $r32Games = [
            [$r32Teams[0], $r32Teams[3], '2026-07-04 14:00:00'],  // 1°A vs 2°B
            [$r32Teams[2], $r32Teams[5], '2026-07-04 18:00:00'],  // 1°C vs 2°D
            [$r32Teams[4], $r32Teams[7], '2026-07-04 21:00:00'],  // 1°E vs 2°F
            [$r32Teams[24], $r32Teams[25], '2026-07-05 14:00:00'],  // Mejor3°1 vs Mejor3°2
            [$r32Teams[1], $r32Teams[2], '2026-07-05 18:00:00'],  // 2°A vs 1°B (corrected)
            [$r32Teams[3], $r32Teams[0], '2026-07-05 21:00:00'],  // 2°B vs 1°A - wait this duplicates...
        ];
        // Actually, let me redo R32 properly without duplicates
        // 1A vs 2B, 2A vs 1B, 1C vs 2D, 2C vs 1D, 1E vs 2F, 2E vs 1F,
        // 1G vs 2H, 2G vs 1H, 1I vs 2J, 2I vs 1J, 1K vs 2L, 2K vs 1L,
        // + 4 games with best 3rd place teams
        $r32GameData = [
            // Jul 4
            [$r32Teams[0], $r32Teams[3], '2026-07-04 14:00:00'],  // 1°A vs 2°B
            [$r32Teams[1], $r32Teams[2], '2026-07-04 18:00:00'],  // 2°A vs 1°B
            // Jul 5
            [$r32Teams[4], $r32Teams[7], '2026-07-05 14:00:00'],  // 1°C vs 2°D
            [$r32Teams[5], $r32Teams[6], '2026-07-05 18:00:00'],  // 2°C vs 1°D
            // Jul 6
            [$r32Teams[8], $r32Teams[11], '2026-07-06 14:00:00'],  // 1°E vs 2°F
            [$r32Teams[9], $r32Teams[10], '2026-07-06 18:00:00'],  // 2°E vs 1°F
            // Jul 7
            [$r32Teams[12], $r32Teams[15], '2026-07-07 14:00:00'],  // 1°G vs 2°H
            [$r32Teams[13], $r32Teams[14], '2026-07-07 18:00:00'],  // 2°G vs 1°H
            // Jul 8
            [$r32Teams[16], $r32Teams[19], '2026-07-08 14:00:00'],  // 1°I vs 2°J
            [$r32Teams[17], $r32Teams[18], '2026-07-08 18:00:00'],  // 2°I vs 1°J
            // Jul 9
            [$r32Teams[20], $r32Teams[23], '2026-07-09 14:00:00'],  // 1°K vs 2°L
            [$r32Teams[21], $r32Teams[22], '2026-07-09 18:00:00'],  // 2°K vs 1°L
            // Jul 10 — Best 3rd place
            [$r32Teams[24], $r32Teams[25], '2026-07-10 14:00:00'],  // M3°1 vs M3°2
            [$r32Teams[26], $r32Teams[27], '2026-07-10 18:00:00'],  // M3°3 vs M3°4
            // Jul 11 — Best 3rd place
            [$r32Teams[28], $r32Teams[29], '2026-07-11 14:00:00'],  // M3°5 vs M3°6
            [$r32Teams[30], $r32Teams[31], '2026-07-11 18:00:00'],  // M3°7 vs M3°8
        ];
        foreach ($r32GameData as $g) {
            Game::updateOrCreate(
                ['home_team_id' => $g[0]->id, 'away_team_id' => $g[1]->id, 'match_date' => Carbon::parse($g[2])],
                ['stage' => 'r32', 'status' => 'pending']
            );
        }

        // — Round of 16 (R16): 8 games — Jul 13–16
        $r16GameData = [
            [$r16Teams[0], $r16Teams[1], '2026-07-13 18:00:00'],
            [$r16Teams[2], $r16Teams[3], '2026-07-13 21:00:00'],
            [$r16Teams[4], $r16Teams[5], '2026-07-14 18:00:00'],
            [$r16Teams[6], $r16Teams[7], '2026-07-14 21:00:00'],
            [$r16Teams[8], $r16Teams[9], '2026-07-15 18:00:00'],
            [$r16Teams[10], $r16Teams[11], '2026-07-15 21:00:00'],
            [$r16Teams[12], $r16Teams[13], '2026-07-16 18:00:00'],
            [$r16Teams[14], $r16Teams[15], '2026-07-16 21:00:00'],
        ];
        foreach ($r16GameData as $g) {
            Game::updateOrCreate(
                ['home_team_id' => $g[0]->id, 'away_team_id' => $g[1]->id, 'match_date' => Carbon::parse($g[2])],
                ['stage' => 'r16', 'status' => 'pending']
            );
        }

        // — Quarterfinals (QF): 4 games — Jul 18–19
        $qfGameData = [
            [$qfTeams[0], $qfTeams[1], '2026-07-18 18:00:00'],
            [$qfTeams[2], $qfTeams[3], '2026-07-18 21:00:00'],
            [$qfTeams[4], $qfTeams[5], '2026-07-19 18:00:00'],
            [$qfTeams[6], $qfTeams[7], '2026-07-19 21:00:00'],
        ];
        foreach ($qfGameData as $g) {
            Game::updateOrCreate(
                ['home_team_id' => $g[0]->id, 'away_team_id' => $g[1]->id, 'match_date' => Carbon::parse($g[2])],
                ['stage' => 'quarter', 'status' => 'pending']
            );
        }

        // — Semifinals (SF): 2 games — Jul 22–23
        $sfGameData = [
            [$sfTeams[0], $sfTeams[1], '2026-07-22 18:00:00'],
            [$sfTeams[2], $sfTeams[3], '2026-07-23 18:00:00'],
        ];
        foreach ($sfGameData as $g) {
            Game::updateOrCreate(
                ['home_team_id' => $g[0]->id, 'away_team_id' => $g[1]->id, 'match_date' => Carbon::parse($g[2])],
                ['stage' => 'semi', 'status' => 'pending']
            );
        }

        // — Third Place: 1 game — Jul 25
        Game::updateOrCreate(
            ['home_team_id' => $thirdHome->id, 'away_team_id' => $thirdAway->id, 'match_date' => Carbon::parse('2026-07-25 15:00:00')],
            ['stage' => 'third_place', 'status' => 'pending']
        );

        // — Final: 1 game — Jul 26
        Game::updateOrCreate(
            ['home_team_id' => $finalHome->id, 'away_team_id' => $finalAway->id, 'match_date' => Carbon::parse('2026-07-26 15:00:00')],
            ['stage' => 'final', 'status' => 'pending']
        );

        // 6. Initialize champion setting
        Setting::setValue('world_cup_champion', null);
    }
}
