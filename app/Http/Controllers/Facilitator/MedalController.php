<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Medal;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedalController extends Controller
{
    private function authorizeGame(Game $game): void
    {
        abort_unless($game->sport_id === Auth::user()->sport_id, 403);
    }

    public function index(Game $game)
    {
        $this->authorizeGame($game);

        $game->load(['goldMedal.team', 'silverMedal.team', 'bronzeMedal.team']);
        $teams = Team::forLevel($game->level)->orderBy('name')->get();

        return view('facilitator.medals', compact('game', 'teams'));
    }

    /** Assign or update all three medals for this game in one submission. */
    public function store(Request $request, Game $game)
    {
        $this->authorizeGame($game);

        $data = $request->validate([
            'medals' => ['required', 'array:gold,silver,bronze'],
            'medals.gold.team_id' => ['required', 'exists:teams,id'],
            'medals.gold.result_image' => ['nullable', 'image', 'max:5120'],
            'medals.silver.team_id' => ['required', 'exists:teams,id'],
            'medals.silver.result_image' => ['nullable', 'image', 'max:5120'],
            'medals.bronze.team_id' => ['required', 'exists:teams,id'],
            'medals.bronze.result_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $eligibleTeamIds = Team::forLevel($game->level)
            ->whereIn('id', collect($data['medals'])->pluck('team_id'))
            ->pluck('id')
            ->all();

        if (count($eligibleTeamIds) !== 3) {
            return back()->withErrors(['medals' => 'Choose teams assigned to the game level.']);
        }

        DB::transaction(function () use ($data, $game): void {
            foreach ($data['medals'] as $type => $medalData) {
                $medal = Medal::firstOrNew(['game_id' => $game->id, 'type' => $type]);
                $resultImage = $medalData['result_image'] ?? null;

                $medal->team_id = $medalData['team_id'];
                $medal->result_image = $resultImage
                    ? $resultImage->store('results/medals', 'public')
                    : $medal->result_image;
                $medal->awarded_at = now();
                $medal->save();
            }

            $game->update([
                'status' => 'completed',
                'manual_medals' => $game->bracket_type !== 'none',
            ]);
        });

        return back()->with('status', 'All medals saved.');
    }

    public function resumeAutomatic(Game $game)
    {
        $this->authorizeGame($game);

        abort_if($game->bracket_type === 'none', 404);

        $game->update(['manual_medals' => false]);

        return back()->with('status', 'Automatic medal assignment will resume when the next match result is saved.');
    }
}
