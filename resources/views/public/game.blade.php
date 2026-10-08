@extends('layouts.sport')
@section('title', $game->name)
@section('content')
<a href="{{ route('public.sport', ['sport' => $sport, 'level' => $game->level]) }}" class="text-sm text-blue-600 hover:underline">&larr; {{ $sport->name }}</a>
<h1 class="text-2xl font-bold mt-2 mb-1">{{ $game->name }}</h1>
<p class="text-slate-500 mb-6 flex items-center gap-2">
    <x-level-badge :level="$game->level" />
    <span>{{ $game->category }}</span>
</p>

{{-- Medal winners --}}
<div class="grid grid-cols-1 gap-4 mb-10 sm:grid-cols-3">
    <div class="bg-white rounded-xl shadow p-5 text-center border-t-4 border-yellow-400">
        <div class="text-3xl">🥇</div>
        <div class="font-bold mt-1">{{ $game->goldMedal->team->name ?? 'TBD' }}</div>
        @if($game->goldMedal && $game->goldMedal->result_image)
            <img src="{{ asset('storage/'.$game->goldMedal->result_image) }}" class="mt-3 rounded-lg mx-auto max-h-40">
        @endif
    </div>
    <div class="bg-white rounded-xl shadow p-5 text-center border-t-4 border-slate-400">
        <div class="text-3xl">🥈</div>
        <div class="font-bold mt-1">{{ $game->silverMedal->team->name ?? 'TBD' }}</div>
        @if($game->silverMedal && $game->silverMedal->result_image)
            <img src="{{ asset('storage/'.$game->silverMedal->result_image) }}" class="mt-3 rounded-lg mx-auto max-h-40">
        @endif
    </div>
    <div class="bg-white rounded-xl shadow p-5 text-center border-t-4 border-amber-700">
        <div class="text-3xl">🥉</div>
        <div class="font-bold mt-1">{{ $game->bronzeMedal->team->name ?? 'TBD' }}</div>
        @if($game->bronzeMedal && $game->bronzeMedal->result_image)
            <img src="{{ asset('storage/'.$game->bronzeMedal->result_image) }}" class="mt-3 rounded-lg mx-auto max-h-40">
        @endif
    </div>
</div>

{{-- Bracket --}}
<h2 class="text-xl font-bold mb-4">{{ str($game->bracket_type)->replace('_', ' ')->title() }} Bracket</h2>
@if($game->bracket_type === 'none')
    <p class="text-slate-400">No bracket for this game.</p>
@else
    <div class="space-y-3">
        @forelse($bracket as $round => $matches)
            <section>
                <h3 class="font-semibold text-slate-500 uppercase text-xs tracking-wide mb-2">{{ str($round)->replace('_', ' ')->title() }}</h3>
                <div class="space-y-3">
                    @foreach($matches as $match)
                        <div class="bg-white rounded-lg shadow p-3 text-sm">
                        <div class="flex justify-between items-center {{ $match->winner_id === $match->team1_id ? 'font-bold text-green-700' : '' }}">
                            <span>{{ $match->team1->name ?? 'TBD' }}</span>
                            <span>{{ $match->team1_score ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center {{ $match->winner_id === $match->team2_id ? 'font-bold text-green-700' : '' }}">
                            <span>{{ $match->team2->name ?? 'TBD' }}</span>
                            <span>{{ $match->team2_score ?? '-' }}</span>
                        </div>
                        @if($match->result_image)
                            <img src="{{ asset('storage/'.$match->result_image) }}" class="mt-2 rounded max-h-28 w-full object-cover">
                        @endif
                        <div class="text-xs text-slate-400 mt-1">{{ ucfirst($match->status) }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-slate-300 text-sm">No matches yet.</p>
        @endforelse
    </div>
@endif
@endsection
