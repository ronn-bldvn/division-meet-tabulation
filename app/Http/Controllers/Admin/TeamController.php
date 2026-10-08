<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::orderBy('name')->get();
        return view('admin.teams.index', compact('teams'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:teams,code',
            'color' => 'required|string|max:7',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('teams', 'public');
        }

        Team::create($data);

        return back()->with('status', 'Division/team added.');
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:teams,code,'.$team->id,
            'color' => 'required|string|max:7',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('teams', 'public');
        }

        $team->update($data);

        return back()->with('status', 'Division/team updated.');
    }

    public function destroy(Team $team)
    {
        $team->delete();
        return back()->with('status', 'Division/team deleted.');
    }
}
