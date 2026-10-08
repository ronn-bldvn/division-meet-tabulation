<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $fillable = ['name', 'slug', 'icon'];

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function facilitators()
    {
        return $this->hasMany(User::class);
    }
}
