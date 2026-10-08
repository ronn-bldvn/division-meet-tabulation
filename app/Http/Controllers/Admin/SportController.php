<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SportController extends Controller
{
    public function index()
    {
        $sports = Sport::withCount('games')->orderBy('name')->get();
        return view('admin.sports.index', compact('sports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);
        $data['slug'] = Str::slug($data['name']);

        Sport::create($data);

        return back()->with('status', 'Sport added.');
    }

    public function update(Request $request, Sport $sport)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);
        $data['slug'] = Str::slug($data['name']);

        $sport->update($data);

        return back()->with('status', 'Sport updated.');
    }

    public function destroy(Sport $sport)
    {
        $sport->delete();
        return back()->with('status', 'Sport deleted.');
    }
}
