<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\GameMatch;
use App\Models\Medal;
use App\Models\Sport;
use App\Models\Team;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TallyController extends Controller
{
    /** Public homepage: overall medal tally across the whole meet. */
    public function index(Request $request)
    {
        $level = Level::normalize($request->query('level'));
        $sportId = $request->integer('sport') ?: null;

        $tally = Team::tally($level, $sportId);
        $sports = Sport::withCount('games')->orderBy('name')->get();
        $eventSetting = EventSetting::first();
        $lastUpdated = collect([GameMatch::max('updated_at'), Medal::max('updated_at')])
            ->filter()
            ->map(fn ($date) => Carbon::parse($date)->setTimezone('Asia/Manila'))
            ->sortDesc()
            ->first();

        return view('public.index', compact('tally', 'sports', 'eventSetting', 'lastUpdated', 'level', 'sportId'));
    }
}
