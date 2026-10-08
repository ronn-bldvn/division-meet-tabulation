<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\Level;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'sports' => Sport::count(),
            'teams' => Team::count(),
            'games' => Game::count(),
            'completed' => Game::where('status', 'completed')->count(),
            'facilitators' => User::where('role', 'facilitator')->count(),
        ];

        $elementaryTally = Team::tally(Level::ELEMENTARY)->take(5);
        $highSchoolTally = Team::tally(Level::HIGH_SCHOOL)->take(5);
        $recentGames = Game::with('sport')->latest()->take(6)->get();
        $eventSetting = EventSetting::firstOrCreate(['id' => 1]);

        return view('admin.dashboard', compact('stats', 'elementaryTally', 'highSchoolTally', 'recentGames', 'eventSetting'));
    }

    public function updateEventSettings(Request $request)
    {
        $data = $request->validate(['event_date' => 'nullable|date']);

        EventSetting::updateOrCreate(['id' => 1], $data);

        return back()->with('status', 'Event date updated.');
    }
}
