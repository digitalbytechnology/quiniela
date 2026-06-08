<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'code', 'group'])]
class Team extends Model
{
    public function homeGames()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function awayGames()
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    public function getRealTeam()
    {
        if ($this->group !== 'TBD') {
            return $this;
        }

        $code = $this->code;

        // 1. Winner of R32 Match XX (wr32-XX)
        if (str_starts_with($code, 'wr32-')) {
            $index = (int) substr($code, 5) - 1;
            $r32Games = Game::where('stage', 'r32')->orderBy('match_date', 'asc')->orderBy('id', 'asc')->get();
            if (isset($r32Games[$index])) {
                $game = $r32Games[$index];
                if ($game->status === 'finished') {
                    $winner = $game->getWinner();
                    return $winner ? $winner->getRealTeam() : $this;
                }
            }
        }

        // 2. Winner of R16 Match XX (wr16-XX)
        if (str_starts_with($code, 'wr16-')) {
            $index = (int) substr($code, 5) - 1;
            $r16Games = Game::where('stage', 'r16')->orderBy('match_date', 'asc')->orderBy('id', 'asc')->get();
            if (isset($r16Games[$index])) {
                $game = $r16Games[$index];
                if ($game->status === 'finished') {
                    $winner = $game->getWinner();
                    return $winner ? $winner->getRealTeam() : $this;
                }
            }
        }

        // 3. Winner of Quarter Match XX (wqf-XX)
        if (str_starts_with($code, 'wqf-')) {
            $index = (int) substr($code, 4) - 1;
            $qfGames = Game::where('stage', 'quarter')->orderBy('match_date', 'asc')->orderBy('id', 'asc')->get();
            if (isset($qfGames[$index])) {
                $game = $qfGames[$index];
                if ($game->status === 'finished') {
                    $winner = $game->getWinner();
                    return $winner ? $winner->getRealTeam() : $this;
                }
            }
        }

        // 4. Winner of SF Match XX (wsf-XX)
        if (str_starts_with($code, 'wsf-')) {
            $index = (int) substr($code, 4) - 1;
            $sfGames = Game::where('stage', 'semi')->orderBy('match_date', 'asc')->orderBy('id', 'asc')->get();
            if (isset($sfGames[$index])) {
                $game = $sfGames[$index];
                if ($game->status === 'finished') {
                    $winner = $game->getWinner();
                    return $winner ? $winner->getRealTeam() : $this;
                }
            }
        }

        // 5. Loser of SF Match XX (lsf-XX)
        if (str_starts_with($code, 'lsf-')) {
            $index = (int) substr($code, 4) - 1;
            $sfGames = Game::where('stage', 'semi')->orderBy('match_date', 'asc')->orderBy('id', 'asc')->get();
            if (isset($sfGames[$index])) {
                $game = $sfGames[$index];
                if ($game->status === 'finished') {
                    $loser = $game->getLoser();
                    return $loser ? $loser->getRealTeam() : $this;
                }
            }
        }

        // 6. Group stage qualifiers (p-1a, p-2a, etc.)
        if (str_starts_with($code, 'p-1') || str_starts_with($code, 'p-2')) {
            $groupLetter = strtoupper(substr($code, 3));
            $totalGroupGames = Game::where('stage', 'group')
                ->whereHas('homeTeam', function ($q) use ($groupLetter) {
                    $q->where('group', $groupLetter);
                })
                ->count();
            $finishedGroupGames = Game::where('stage', 'group')
                ->where('status', 'finished')
                ->whereHas('homeTeam', function ($q) use ($groupLetter) {
                    $q->where('group', $groupLetter);
                })
                ->count();

            if ($totalGroupGames > 0 && $totalGroupGames === $finishedGroupGames) {
                $teamsInGroup = Team::where('group', $groupLetter)->get();
                $standings = [];
                foreach ($teamsInGroup as $t) {
                    $standings[$t->id] = [
                        'team' => $t,
                        'pts' => 0,
                        'gf' => 0,
                        'ga' => 0,
                        'gd' => 0
                    ];
                }

                $groupGames = Game::where('stage', 'group')
                    ->where('status', 'finished')
                    ->whereHas('homeTeam', function ($q) use ($groupLetter) {
                        $q->where('group', $groupLetter);
                    })
                    ->get();

                foreach ($groupGames as $g) {
                    $hId = $g->home_team_id;
                    $aId = $g->away_team_id;
                    $hScore = $g->home_score;
                    $aScore = $g->away_score;

                    $standings[$hId]['gf'] += $hScore;
                    $standings[$hId]['ga'] += $aScore;
                    $standings[$aId]['gf'] += $aScore;
                    $standings[$aId]['ga'] += $hScore;

                    if ($hScore > $aScore) {
                        $standings[$hId]['pts'] += 3;
                    } elseif ($aScore > $hScore) {
                        $standings[$aId]['pts'] += 3;
                    } else {
                        $standings[$hId]['pts'] += 1;
                        $standings[$aId]['pts'] += 1;
                    }
                }

                foreach ($standings as $id => $data) {
                    $standings[$id]['gd'] = $data['gf'] - $data['ga'];
                }

                uasort($standings, function ($a, $b) {
                    if ($a['pts'] !== $b['pts']) {
                        return $b['pts'] - $a['pts'];
                    }
                    if ($a['gd'] !== $b['gd']) {
                        return $b['gd'] - $a['gd'];
                    }
                    return $b['gf'] - $a['gf'];
                });

                $sortedTeams = array_values($standings);
                if (str_starts_with($code, 'p-1')) {
                    return $sortedTeams[0]['team']->getRealTeam();
                } else {
                    return $sortedTeams[1]['team']->getRealTeam();
                }
            }
        }

        // 7. Best 3rd place qualifiers (p-3rd-1 to p-3rd-8)
        if (str_starts_with($code, 'p-3rd-')) {
            $thirdIndex = (int) substr($code, 6) - 1;
            $totalGroupGames = Game::where('stage', 'group')->count();
            $finishedGroupGames = Game::where('stage', 'group')->where('status', 'finished')->count();

            if ($totalGroupGames > 0 && $totalGroupGames === $finishedGroupGames) {
                $thirdPlaceTeams = [];
                $groups = ['A','B','C','D','E','F','G','H','I','J','K','L'];

                foreach ($groups as $groupLetter) {
                    $teamsInGroup = Team::where('group', $groupLetter)->get();
                    $standings = [];
                    foreach ($teamsInGroup as $t) {
                        $standings[$t->id] = [
                            'team' => $t,
                            'pts' => 0,
                            'gf' => 0,
                            'ga' => 0,
                            'gd' => 0
                        ];
                    }

                    $groupGames = Game::where('stage', 'group')
                        ->where('status', 'finished')
                        ->whereHas('homeTeam', function ($q) use ($groupLetter) {
                            $q->where('group', $groupLetter);
                        })
                        ->get();

                    foreach ($groupGames as $g) {
                        $hId = $g->home_team_id;
                        $aId = $g->away_team_id;
                        $hScore = $g->home_score;
                        $aScore = $g->away_score;

                        $standings[$hId]['gf'] += $hScore;
                        $standings[$hId]['ga'] += $aScore;
                        $standings[$aId]['gf'] += $aScore;
                        $standings[$aId]['ga'] += $hScore;

                        if ($hScore > $aScore) {
                            $standings[$hId]['pts'] += 3;
                        } elseif ($aScore > $hScore) {
                            $standings[$aId]['pts'] += 3;
                        } else {
                            $standings[$hId]['pts'] += 1;
                            $standings[$aId]['pts'] += 1;
                        }
                    }

                    foreach ($standings as $id => $data) {
                        $standings[$id]['gd'] = $data['gf'] - $data['ga'];
                    }

                    uasort($standings, function ($a, $b) {
                        if ($a['pts'] !== $b['pts']) {
                            return $b['pts'] - $a['pts'];
                        }
                        if ($a['gd'] !== $b['gd']) {
                            return $b['gd'] - $a['gd'];
                        }
                        return $b['gf'] - $a['gf'];
                    });

                    $sortedGroup = array_values($standings);
                    if (isset($sortedGroup[2])) {
                        $thirdPlaceTeams[] = $sortedGroup[2];
                    }
                }

                uasort($thirdPlaceTeams, function ($a, $b) {
                    if ($a['pts'] !== $b['pts']) {
                        return $b['pts'] - $a['pts'];
                    }
                    if ($a['gd'] !== $b['gd']) {
                        return $b['gd'] - $a['gd'];
                    }
                    return $b['gf'] - $a['gf'];
                });

                $bestThirds = array_values($thirdPlaceTeams);
                if (isset($bestThirds[$thirdIndex])) {
                    return $bestThirds[$thirdIndex]['team']->getRealTeam();
                }
            }
        }

        return $this;
    }
}
