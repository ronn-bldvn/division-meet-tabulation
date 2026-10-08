<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medal extends Model
{
    protected $fillable = ['game_id', 'team_id', 'type', 'result_image', 'awarded_at'];

    protected $casts = ['awarded_at' => 'datetime'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
