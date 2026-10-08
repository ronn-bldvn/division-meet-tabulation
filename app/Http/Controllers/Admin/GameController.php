<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Sport;
use App\Support\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with('sport')->latest()->get();
        $sports = Sport::orderBy('name')->get();

        return view('admin.games.index', compact('games', 'sports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'level' => ['required', Rule::in(Level::keys())],
            'bracket_type' => 'required|in:single_elimination,double_elimination,round_robin,none',
        ]);

        Game::create($data);

        return back()->with('status', 'Game/event added.');
    }

    public function update(Request $request, Game $game)
    {
        $data = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'level' => ['required', Rule::in(Level::keys())],
            'bracket_type' => 'required|in:single_elimination,double_elimination,round_robin,none',
            'status' => 'required|in:upcoming,ongoing,completed',
        ]);

        if ($data['bracket_type'] !== $game->bracket_type
            && ($game->matches()->exists() || $game->medals()->exists())) {
            throw ValidationException::withMessages([
                'bracket_type' => 'Remove existing matches and medals before changing the bracket format.',
            ]);
        }

        if ($data['level'] !== $game->level && ($game->matches()->exists() || $game->medals()->exists())) {
            throw ValidationException::withMessages([
                'level' => 'Remove existing matches and medals before changing the game level.',
            ]);
        }

        $game->update($data);

        return back()->with('status', 'Game/event updated.');
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return back()->with('status', 'Game/event deleted.');
    }
}
