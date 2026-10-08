@extends('layouts.app')
@section('title', 'Manage Games')
@section('content')
@php use App\Support\Level; @endphp
<h1 class="text-2xl font-bold mb-6">Manage Games / Events</h1>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <h2 class="font-semibold mb-3">Add Game</h2>
    <form method="POST" action="{{ route('admin.games.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium mb-1" for="sport_id">Sport</label>
            <select id="sport_id" name="sport_id" required class="w-full rounded-lg border-slate-300 text-sm">
                <option value="">Select sport</option>
                @foreach($sports as $sport)<option value="{{ $sport->id }}">{{ $sport->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="level">Level</label>
            <select id="level" name="level" required class="w-full rounded-lg border-slate-300 text-sm">
                @foreach(Level::ALL as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="name">Event name</label>
            <input id="name" type="text" name="name" placeholder="e.g. Basketball - Boys" required class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="category">Category</label>
            <input id="category" type="text" name="category" placeholder="Boys / Girls / Open" class="w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" for="bracket_type">Bracket</label>
            <select id="bracket_type" name="bracket_type" class="w-full rounded-lg border-slate-300 text-sm">
                <option value="single_elimination">Single Elimination</option>
                <option value="double_elimination">Double Elimination</option>
                <option value="round_robin">Round Robin</option>
                <option value="none">None</option>
            </select>
        </div>
        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg py-2 text-sm font-medium">Add</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow divide-y divide-slate-100">
    @forelse($games as $game)
        <div class="p-4 flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-medium">{{ $game->sport->name }} — {{ $game->name }}</span>
                    <x-level-badge :level="$game->level" />
                </div>
                <div class="text-xs text-slate-400">{{ $game->category }} · {{ str_replace('_',' ', $game->bracket_type) }}</div>
            </div>
            <div class="flex items-center gap-3">
                <details class="relative">
                    <summary class="cursor-pointer text-blue-600 hover:underline text-sm">Edit</summary>
                    <form method="POST" action="{{ route('admin.games.update', $game) }}" class="absolute right-0 z-10 mt-2 grid w-72 gap-3 rounded-xl border bg-white p-4 shadow-lg">
                        @csrf @method('PUT')
                        <label class="text-xs font-medium">Sport
                            <select name="sport_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                @foreach($sports as $sport)
                                    <option value="{{ $sport->id }}" @selected($game->sport_id === $sport->id)>{{ $sport->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs font-medium">Event name
                            <input type="text" name="name" value="{{ $game->name }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Category
                            <input type="text" name="category" value="{{ $game->category }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Level
                            <select name="level" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                @foreach(Level::ALL as $value => $label)
                                    <option value="{{ $value }}" @selected($game->level === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-xs font-medium">Bracket
                            <select name="bracket_type" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="single_elimination" @selected($game->bracket_type === 'single_elimination')>Single Elimination</option>
                                <option value="double_elimination" @selected($game->bracket_type === 'double_elimination')>Double Elimination</option>
                                <option value="round_robin" @selected($game->bracket_type === 'round_robin')>Round Robin</option>
                                <option value="none" @selected($game->bracket_type === 'none')>None</option>
                            </select>
                        </label>
                        <label class="text-xs font-medium">Status
                            <select name="status" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="upcoming" @selected($game->status === 'upcoming')>Upcoming</option>
                                <option value="ongoing" @selected($game->status === 'ongoing')>Ongoing</option>
                                <option value="completed" @selected($game->status === 'completed')>Completed</option>
                            </select>
                        </label>
                        <button class="rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-500">Save changes</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('admin.games.destroy', $game) }}" onsubmit="return confirm('Delete this game and all its matches/medals?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:underline text-sm">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <p class="p-4 text-slate-400 text-sm">No games added yet.</p>
    @endforelse
</div>
@endsection
