@extends('layouts.sport')
@section('title', $sport->name)
@section('content')
@php use App\Support\Level; @endphp

<a href="{{ route('public.tally', array_filter(['level' => $level])) }}" class="text-sm text-blue-600 hover:underline">&larr; Overall Tally</a>
<h1 class="text-2xl font-bold mt-2 mb-4">{{ $sport->icon ?? '' }} {{ $sport->name }}</h1>

{{-- Level filter: All / Elementary / High School --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach(array_merge(['all' => 'All'], Level::ALL) as $value => $label)
        @php $active = ($level ?? 'all') === $value; @endphp
        <a href="{{ route('public.sport', array_merge(['sport' => $sport], $value === 'all' ? [] : ['level' => $value])) }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium border
                  {{ $active ? 'bg-green-700 text-white border-green-700' : 'bg-white text-slate-600 border-slate-300 hover:border-green-400' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@php
    $grouped = $games->groupBy('level');
@endphp

@if($games->isEmpty())
    <p class="text-slate-400">
        No games found{{ $level ? ' for '.Level::label($level) : ' for this sport' }} yet.
    </p>
@else
    <div class="grid gap-6">
        @foreach(array_keys(Level::ALL) as $levelKey)
            @php $gradeGames = $grouped->get($levelKey, collect()); @endphp
            @if($gradeGames->isNotEmpty())
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">
                        {{ Level::label($levelKey) }}
                    </h2>
                    <div class="grid gap-4">
                        @foreach($gradeGames as $game)
                            <a href="{{ route('public.game', [$sport, $game]) }}" class="bg-white rounded-xl shadow p-4 flex flex-col items-start gap-4 hover:shadow-md transition sm:flex-row sm:items-center sm:justify-between sm:p-5">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold">{{ $game->name }}</span>
                                        <x-level-badge :level="$game->level" />
                                    </div>
                                    <div class="text-sm text-slate-400">{{ $game->category }}</div>
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full
                                        {{ $game->status === 'completed' ? 'bg-green-100 text-green-700' : ($game->status === 'ongoing' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500') }}">
                                        {{ ucfirst($game->status) }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm sm:justify-end sm:text-right">
                                    <div>🥇 {{ $game->goldMedal->team->name ?? '—' }}</div>
                                    <div>🥈 {{ $game->silverMedal->team->name ?? '—' }}</div>
                                    <div>🥉 {{ $game->bronzeMedal->team->name ?? '—' }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
@endif
@endsection
