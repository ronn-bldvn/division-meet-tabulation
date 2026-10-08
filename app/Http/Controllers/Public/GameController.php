<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Support\Level;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /** All games for one sport, with medal winners at a glance. */
    public function index(Request $request, Sport $sport)
    {
        $level = Level::normalize($request->query('level'));

        $games = $sport->games()
            ->forLevel($level)
            ->with(['goldMedal.team', 'silverMedal.team', 'bronzeMedal.team'])
            ->get();

        return view('public.sport', compact('sport', 'games', 'level'));
    }

    /** Single game: bracket + medal results + result photos. */
    public function show(Sport $sport, \App\Models\Game $game)
    {
        abort_unless($game->sport_id === $sport->id, 404);

        $game->load([
            'matches.team1', 'matches.team2', 'matches.winner',
            'goldMedal.team', 'silverMedal.team', 'bronzeMedal.team',
        ]);

        $bracket = $game->matches->groupBy('round');

        return view('public.game', compact('sport', 'game', 'bracket'));
    }
}
