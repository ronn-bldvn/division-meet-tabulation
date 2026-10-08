@extends('layouts.app')
@section('title', $game->name.' — Bracket')
@section('content')
<a href="{{ route('facilitator.dashboard') }}" class="text-sm text-blue-600 hover:underline">&larr; My Sport</a>
<h1 class="text-2xl font-bold mt-2 mb-2 flex flex-wrap items-center gap-3">{{ $game->name }} — Bracket Management <x-level-badge :level="$game->level" /></h1>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-slate-500">
        Format: {{ str($game->bracket_type)->replace('_', ' ')->title() }}
        @if($game->bracket_type === 'single_elimination')
            — winners advance automatically.
        @elseif($game->bracket_type === 'double_elimination')
            — route each winner and loser to a match slot. The grand final reset is created when needed.
        @elseif($game->bracket_type === 'round_robin')
            — medals are awarded automatically after all scheduled matches are complete; ties use head-to-head results.
        @endif
    </p>

    @if($game->matches->isEmpty() && $game->medals->isEmpty())
        <form method="POST" action="{{ route('facilitator.bracket.update', $game) }}" class="flex flex-wrap items-center gap-2">
            @csrf @method('PUT')
            <label for="bracket_type" class="text-xs font-medium">Bracket format</label>
            <select id="bracket_type" name="bracket_type" class="rounded-lg border-slate-300 text-sm">
                <option value="none" @selected($game->bracket_type === 'none')>No bracket</option>
                <option value="single_elimination" @selected($game->bracket_type === 'single_elimination')>Single elimination</option>
                <option value="double_elimination" @selected($game->bracket_type === 'double_elimination')>Double elimination</option>
                <option value="round_robin" @selected($game->bracket_type === 'round_robin')>Round robin</option>
            </select>
            <button class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white">Save</button>
        </form>
    @endif
</div>

@if($game->bracket_type !== 'none')
    <div class="bg-white rounded-xl shadow p-5 mb-8">
        <h2 class="font-semibold mb-3">{{ $game->bracket_type === 'round_robin' ? 'Add Round-Robin Match' : 'Add Match to Bracket' }}</h2>
        <form method="POST" action="{{ route('facilitator.matches.store', $game) }}" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
            @csrf
            <label class="text-xs font-medium">Round
                <select name="round" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
                    @foreach($roundOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
            </label>
            <label class="text-xs font-medium">Match #
                <input type="number" name="match_number" min="1" value="1" class="mt-1 w-full rounded-lg border-slate-300 text-sm" required>
            </label>
            <label class="text-xs font-medium">Team 1
                <select name="team1_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">TBD</option>
                    @foreach($teams as $team)<option value="{{ $team->id }}">{{ $team->code }} — {{ $team->name }}</option>@endforeach
                </select>
            </label>
            <label class="text-xs font-medium">Team 2
                <select name="team2_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">TBD</option>
                    @foreach($teams as $team)<option value="{{ $team->id }}">{{ $team->code }} — {{ $team->name }}</option>@endforeach
                </select>
            </label>
            @if($game->bracket_type === 'double_elimination')
                <label class="text-xs font-medium">Winner advances to
                    <select name="winner_next_match_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        <option value="">No destination</option>
                        @foreach($game->matches as $target)
                            <option value="{{ $target->id }}">Match {{ $target->match_number }} — {{ $roundOptions[$target->round] ?? str($target->round)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium">Winner slot
                    <select name="winner_next_slot" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        <option value="">—</option><option value="team1">Team 1</option><option value="team2">Team 2</option>
                    </select>
                </label>
                <label class="text-xs font-medium">Loser advances to
                    <select name="loser_next_match_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        <option value="">No destination</option>
                        @foreach($game->matches as $target)
                            <option value="{{ $target->id }}">Match {{ $target->match_number }} — {{ $roundOptions[$target->round] ?? str($target->round)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium">Loser slot
                    <select name="loser_next_slot" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        <option value="">—</option><option value="team1">Team 1</option><option value="team2">Team 2</option>
                    </select>
                </label>
            @endif
            <button class="rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-500">Add Match</button>
        </form>
    </div>
@else
    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        This event has no bracket. Assign gold, silver, and bronze from the Medals page; only teams for this level are available.
    </div>
@endif

@if($game->bracket_type === 'none')
    <p class="text-slate-400">No bracket matches configured.</p>
@elseif($bracket->isEmpty())
    <p class="text-slate-400">No matches have been added yet.</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($bracket as $round => $matches)
            <section>
                <h3 class="font-semibold text-slate-500 uppercase text-xs tracking-wide mb-2">
                    {{ $roundOptions[$round] ?? str($round)->replace('_', ' ')->title() }}
                </h3>
                <div class="space-y-4">
                    @foreach($matches as $match)
                        <div class="bg-white rounded-lg shadow p-4 text-sm">
                            <div class="text-xs text-slate-400 mb-2">Match #{{ $match->match_number }} · ID {{ $match->id }}</div>
                            <form method="POST" action="{{ route('facilitator.matches.update', [$game, $match]) }}" enctype="multipart/form-data" class="space-y-2">
                                @csrf @method('PUT')
                                <label class="block text-xs font-medium">Round
                                    <select name="round" class="mt-1 w-full rounded border-slate-300 text-xs" required>
                                        @foreach($roundOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($match->round === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block text-xs font-medium">Match #
                                    <input type="number" name="match_number" min="1" value="{{ $match->match_number }}" class="mt-1 w-full rounded border-slate-300 text-xs" required>
                                </label>
                                <label class="block text-xs font-medium">Team 1
                                    <select name="team1_id" class="mt-1 w-full rounded border-slate-300 text-xs">
                                        <option value="">TBD</option>
                                        @foreach($teams as $team)<option value="{{ $team->id }}" @selected($match->team1_id === $team->id)>{{ $team->code }} — {{ $team->name }}</option>@endforeach
                                    </select>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="flex-1 text-xs text-slate-500">{{ $match->team1->name ?? 'TBD' }}</span>
                                    <input type="number" name="team1_score" value="{{ $match->team1_score }}" class="w-16 rounded border-slate-300 text-sm" min="0" aria-label="Team 1 score">
                                </div>
                                <label class="block text-xs font-medium">Team 2
                                    <select name="team2_id" class="mt-1 w-full rounded border-slate-300 text-xs">
                                        <option value="">TBD</option>
                                        @foreach($teams as $team)<option value="{{ $team->id }}" @selected($match->team2_id === $team->id)>{{ $team->code }} — {{ $team->name }}</option>@endforeach
                                    </select>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="flex-1 text-xs text-slate-500">{{ $match->team2->name ?? 'TBD' }}</span>
                                    <input type="number" name="team2_score" value="{{ $match->team2_score }}" class="w-16 rounded border-slate-300 text-sm" min="0" aria-label="Team 2 score">
                                </div>
                                <select name="winner_id" class="w-full rounded border-slate-300 text-xs">
                                    <option value="">Winner: TBD</option>
                                    @if($match->team1)<option value="{{ $match->team1_id }}" @selected($match->winner_id === $match->team1_id)>{{ $match->team1->name }}</option>@endif
                                    @if($match->team2)<option value="{{ $match->team2_id }}" @selected($match->winner_id === $match->team2_id)>{{ $match->team2->name }}</option>@endif
                                </select>
                                <select name="status" class="w-full rounded border-slate-300 text-xs">
                                    <option value="scheduled" @selected($match->status === 'scheduled')>Scheduled</option>
                                    <option value="ongoing" @selected($match->status === 'ongoing')>Ongoing</option>
                                    <option value="completed" @selected($match->status === 'completed')>Completed</option>
                                </select>
                                @if($game->bracket_type === 'double_elimination')
                                    <label class="block text-xs font-medium">Winner advances to
                                        <select name="winner_next_match_id" class="mt-1 w-full rounded border-slate-300 text-xs">
                                            <option value="">No destination</option>
                                            @foreach($game->matches->where('id', '!=', $match->id) as $target)
                                                <option value="{{ $target->id }}" @selected($match->winner_next_match_id === $target->id)>Match {{ $target->match_number }} — {{ $roundOptions[$target->round] ?? str($target->round)->replace('_', ' ')->title() }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <select name="winner_next_slot" class="w-full rounded border-slate-300 text-xs" aria-label="Winner destination team slot">
                                        <option value="">Winner slot —</option>
                                        <option value="team1" @selected($match->winner_next_slot === 'team1')>Winner fills Team 1</option>
                                        <option value="team2" @selected($match->winner_next_slot === 'team2')>Winner fills Team 2</option>
                                    </select>
                                    <label class="block text-xs font-medium">Loser advances to
                                        <select name="loser_next_match_id" class="mt-1 w-full rounded border-slate-300 text-xs">
                                            <option value="">No destination</option>
                                            @foreach($game->matches->where('id', '!=', $match->id) as $target)
                                                <option value="{{ $target->id }}" @selected($match->loser_next_match_id === $target->id)>Match {{ $target->match_number }} — {{ $roundOptions[$target->round] ?? str($target->round)->replace('_', ' ')->title() }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <select name="loser_next_slot" class="w-full rounded border-slate-300 text-xs" aria-label="Loser destination team slot">
                                        <option value="">Loser slot —</option>
                                        <option value="team1" @selected($match->loser_next_slot === 'team1')>Loser fills Team 1</option>
                                        <option value="team2" @selected($match->loser_next_slot === 'team2')>Loser fills Team 2</option>
                                    </select>
                                @else
                                    <input type="hidden" name="winner_next_match_id" value="{{ $match->winner_next_match_id }}">
                                    <input type="hidden" name="winner_next_slot" value="{{ $match->winner_next_slot }}">
                                    <input type="hidden" name="loser_next_match_id" value="{{ $match->loser_next_match_id }}">
                                    <input type="hidden" name="loser_next_slot" value="{{ $match->loser_next_slot }}">
                                @endif
                                <input type="file" name="result_image" accept="image/*" class="w-full text-xs">
                                @if($match->result_image)
                                    <img src="{{ asset('storage/'.$match->result_image) }}" class="rounded max-h-24 w-full object-cover">
                                @endif
                                <button class="w-full bg-slate-800 hover:bg-slate-700 text-white rounded py-1.5 text-xs font-medium">Save Match / Result</button>
                            </form>
                            <form method="POST" action="{{ route('facilitator.matches.destroy', [$game, $match]) }}" class="mt-1" onsubmit="return confirm('Remove this match?')">
                                @csrf @method('DELETE')
                                <button class="w-full text-red-500 hover:text-red-700 text-xs py-1">Remove match</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endif
@endsection
