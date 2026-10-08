@extends('layouts.app')
@section('title', $game->name.' — Medals')
@section('content')
<a href="{{ route('facilitator.dashboard') }}" class="text-sm text-blue-600 hover:underline">&larr; My Sport</a>
<h1 class="text-2xl font-bold mt-2 mb-2 flex flex-wrap items-center gap-3">{{ $game->name }} — Assign Medals <x-level-badge :level="$game->level" /></h1>

@if($game->bracket_type !== 'none')
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 mb-6 text-sm">
        @if($game->bracket_type === 'round_robin')
            Complete every scheduled match. Medals are calculated from match wins, with head-to-head results resolving ties.
        @elseif($game->bracket_type === 'double_elimination')
            Complete the grand final (and reset final if required) to award gold and silver automatically. Bronze comes from the losers' final.
        @else
            Complete the final in the bracket to award gold, silver, and bronze automatically from the match winners.
        @endif
    </div>
@else

<form method="POST" action="{{ route('facilitator.medals.store', $game) }}" enctype="multipart/form-data">
    @csrf
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach(['gold' => ['🥇','Gold', $game->goldMedal], 'silver' => ['🥈','Silver', $game->silverMedal], 'bronze' => ['🥉','Bronze', $game->bronzeMedal]] as $type => [$emoji, $label, $medal])
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-3xl mb-2">{{ $emoji }}</div>
            <h2 class="font-semibold mb-3">{{ $label }} Medal</h2>
                <select name="medals[{{ $type }}][team_id]" class="w-full rounded-lg border-slate-300 text-sm" required>
                    <option value="">Select {{ \App\Support\Level::label($game->level) }} team</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected(optional($medal)->team_id === $team->id)>{{ $team->code }} — {{ $team->name }}</option>
                    @endforeach
                </select>
                <input type="file" name="medals[{{ $type }}][result_image]" accept="image/*" class="w-full text-xs">
                @if($medal && $medal->result_image)
                    <img src="{{ asset('storage/'.$medal->result_image) }}" class="rounded-lg max-h-40 w-full object-cover">
                @endif
        </div>
    @endforeach
</div>
    <button class="mt-6 w-full bg-yellow-500 hover:bg-yellow-400 text-white rounded-lg py-3 text-sm font-medium">Save All Medals</button>
</form>
@endif
@endsection
