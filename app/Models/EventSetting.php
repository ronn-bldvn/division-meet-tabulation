<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    protected $fillable = ['event_date'];

    protected $casts = ['event_date' => 'date'];
}