<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $sport = Auth::user()->sport;

        abort_unless($sport, 403, 'No sport is assigned to your account yet. Contact the super admin.');

        $games = $sport->games()
            ->withCount('matches')
            ->with(['goldMedal', 'silverMedal', 'bronzeMedal'])
            ->get();

        return view('facilitator.dashboard', compact('sport', 'games'));
    }
}
