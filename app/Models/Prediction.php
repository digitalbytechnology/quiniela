<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'game_id', 'home_score', 'away_score', 'points_earned'])]
class Prediction extends Model
{
    protected function casts(): array
    {
        return [
            'home_score' => 'integer',
            'away_score' => 'integer',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
