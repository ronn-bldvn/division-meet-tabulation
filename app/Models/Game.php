<?php

namespace App\Models;

use App\Support\Level;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['sport_id', 'name', 'category', 'level', 'bracket_type', 'status'];

    /** Human-readable label for this game's level (Elementary / High School). */
    public function levelLabel(): string
    {
        return Level::label($this->level);
    }

    /** Restrict a query to a single level; ignores null/unknown values. */
    public function scopeForLevel(Builder $query, ?string $level): Builder
    {
        $level = Level::normalize($level);

        return $level ? $query->where('level', $level) : $query;
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function matches()
    {
        return $this->hasMany(GameMatch::class)->orderBy('round')->orderBy('match_number');
    }

    public function medals()
    {
        return $this->hasMany(Medal::class);
    }

    public function goldMedal()
    {
        return $this->hasOne(Medal::class)->where('type', 'gold');
    }

    public function silverMedal()
    {
        return $this->hasOne(Medal::class)->where('type', 'silver');
    }

    public function bronzeMedal()
    {
        return $this->hasOne(Medal::class)->where('type', 'bronze');
    }

    /** Matches grouped by round, ready for bracket rendering. */
    public function bracket()
    {
        return $this->matches()->get()->groupBy('round');
    }
}
