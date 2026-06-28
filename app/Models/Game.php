<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['home_team_id', 'away_team_id', 'home_score', 'away_score', 'match_date', 'stage', 'status', 'winner_id'])]
class Game extends Model
{
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'winner_id' => 'integer',
        ];
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public static function getUnlockedStages(): array
    {
        $stagesOrder = ['group', 'r32', 'r16', 'quarter', 'semi'];
        $unlocked = ['group'];

        foreach ($stagesOrder as $stage) {
            $gamesExist = self::where('stage', $stage)->exists();
            $hasUnfinished = $gamesExist ? self::where('stage', $stage)->where('status', '!=', 'finished')->exists() : false;

            // Si la fase no existe (ej. se borró la fase de grupos) o ya está finalizada, desbloqueamos la siguiente
            if (!$gamesExist || !$hasUnfinished) {
                if ($stage === 'group') {
                    $unlocked[] = 'r32';
                } elseif ($stage === 'r32') {
                    $unlocked[] = 'r16';
                } elseif ($stage === 'r16') {
                    $unlocked[] = 'quarter';
                } elseif ($stage === 'quarter') {
                    $unlocked[] = 'semi';
                } elseif ($stage === 'semi') {
                    $unlocked[] = 'third_place';
                    $unlocked[] = 'final';
                }
            } else {
                // Si la fase actual existe y tiene partidos pendientes, no desbloqueamos las siguientes
                break;
            }
        }

        return $unlocked;
    }

    public function getWinner()
    {
        if ($this->winner_id) {
            return $this->winner;
        }

        if ($this->status !== 'finished') {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->homeTeam;
        } elseif ($this->away_score > $this->home_score) {
            return $this->awayTeam;
        }

        return $this->homeTeam;
    }

    public function getLoser()
    {
        if ($this->status !== 'finished') {
            return null;
        }

        $winner = $this->getWinner();
        if ($winner) {
            return ($winner->id === $this->home_team_id) ? $this->awayTeam : $this->homeTeam;
        }

        if ($this->home_score < $this->away_score) {
            return $this->homeTeam;
        } elseif ($this->away_score < $this->home_score) {
            return $this->awayTeam;
        }

        return $this->awayTeam;
    }
}
