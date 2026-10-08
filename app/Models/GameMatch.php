<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    protected $table = 'game_matches';

    protected $fillable = [
        'game_id', 'round', 'match_number', 'team1_id', 'team2_id',
        'team1_score', 'team2_score', 'winner_id', 'status',
        'result_image', 'scheduled_at', 'next_match_id',
        'winner_next_match_id', 'winner_next_slot',
        'loser_next_match_id', 'loser_next_slot',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function nextMatch()
    {
        return $this->belongsTo(GameMatch::class, 'next_match_id');
    }

    public function winnerNextMatch()
    {
        return $this->belongsTo(GameMatch::class, 'winner_next_match_id');
    }

    public function loserNextMatch()
    {
        return $this->belongsTo(GameMatch::class, 'loser_next_match_id');
    }
}
