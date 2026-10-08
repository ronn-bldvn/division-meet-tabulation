<?php

namespace App\Models;

use App\Support\Level;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name', 'code', 'color', 'logo'];

    public function medals()
    {
        return $this->hasMany(Medal::class);
    }

    public function matchesAsTeam1()
    {
        return $this->hasMany(GameMatch::class, 'team1_id');
    }

    public function matchesAsTeam2()
    {
        return $this->hasMany(GameMatch::class, 'team2_id');
    }

    public function scopeForLevel($query, ?string $level)
    {
        if ($level === Level::ELEMENTARY) {
            return $query->whereRaw('LOWER(code) LIKE ?', ['elemteam%']);
        }

        if ($level === Level::HIGH_SCHOOL) {
            return $query->whereRaw('LOWER(code) LIKE ?', ['hsteam%']);
        }

        return $query;
    }

    /**
     * Build the public overall medal tally, sorted Olympic-style:
     * most golds first, then silvers, then bronzes.
     *
     * Optionally scope the tally to a level (Elementary / High School)
     * and/or a single sport, so the public page can show per-level tallies.
     */
    public static function tally(?string $level = null, ?int $sportId = null)
    {
        $level = Level::normalize($level);

        $constrain = function ($query) use ($level, $sportId) {
            if ($level) {
                $query->whereHas('game', fn ($game) => $game->where('level', $level));
            }
            if ($sportId) {
                $query->whereHas('game', fn ($game) => $game->where('sport_id', $sportId));
            }

            return $query;
        };

        $teams = self::query()->forLevel($level)->withCount([
            'medals as gold_count' => fn ($q) => $constrain($q)->where('type', 'gold'),
            'medals as silver_count' => fn ($q) => $constrain($q)->where('type', 'silver'),
            'medals as bronze_count' => fn ($q) => $constrain($q)->where('type', 'bronze'),
        ])
            ->get()
            ->map(function ($team) {
                $team->total_medals = $team->gold_count + $team->silver_count + $team->bronze_count;

                return $team;
            })
            ->sortBy([
                ['gold_count', 'desc'],
                ['silver_count', 'desc'],
                ['bronze_count', 'desc'],
            ])
            ->values();

        return $teams;
    }
}
