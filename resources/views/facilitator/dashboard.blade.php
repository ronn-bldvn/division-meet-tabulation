@extends('layouts.app')
@section('title', $sport->name.' — Facilitator')
@section('content')
@php use App\Support\Level; @endphp
<h1 class="text-2xl font-bold mb-1">{{ $sport->icon ?? '🏅' }} {{ $sport->name }}</h1>
<p class="text-slate-500 mb-6">Manage brackets and medal results for your assigned sport.</p>

@php
    $grouped = $games->groupBy('level');
@endphp

@if($games->isEmpty())
    <p class="text-slate-400">No games assigned yet. Ask the super admin to create games for {{ $sport->name }}.</p>
@else
    <div class="grid gap-8">
        @foreach(array_keys(Level::ALL) as $levelKey)
            @php $levelGames = $grouped->get($levelKey, collect()); @endphp
            @if($levelGames->isNotEmpty())
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">
                        {{ Level::label($levelKey) }}
                    </h2>
                    <div class="grid gap-4">
                        @foreach($levelGames as $game)
                            <div class="bg-white rounded-xl shadow p-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold">{{ $game->name }}</span>
                                        <x-level-badge :level="$game->level" />
                                    </div>
                                    <div class="text-sm text-slate-400">{{ $game->category }} · {{ $game->matches_count }} match(es)</div>
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full
                                        {{ $game->status === 'completed' ? 'bg-green-100 text-green-700' : ($game->status === 'ongoing' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500') }}">
                                        {{ ucfirst($game->status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 sm:flex">
                                    <a href="{{ route('facilitator.matches.index', $game) }}" class="px-3 py-2 rounded-lg bg-slate-800 text-white text-sm hover:bg-slate-700">Bracket</a>
                                    <a href="{{ route('facilitator.medals.index', $game) }}" class="px-3 py-2 rounded-lg bg-yellow-500 text-white text-sm hover:bg-yellow-400">Medals</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
@endif
@endsection
