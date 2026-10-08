@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<h1 class="text-2xl font-bold mb-6">Super Admin Dashboard</h1>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <h2 class="font-semibold mb-3">Meet Details</h2>
    <form method="POST" action="{{ route('admin.event-settings.update') }}" class="flex flex-wrap items-end gap-3">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium mb-1" for="event_date">Event date</label>
            <input id="event_date" type="date" name="event_date" value="{{ optional($eventSetting->event_date)->format('Y-m-d') }}" class="rounded-lg border-slate-300 text-sm">
        </div>
        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg px-4 py-2 text-sm font-medium">Save date</button>
    </form>
</div>

<div class="grid grid-cols-1 min-[420px]:grid-cols-2 md:grid-cols-5 gap-4 mb-10">
    @foreach(['sports'=>'Sports','teams'=>'Divisions','games'=>'Games','completed'=>'Completed','facilitators'=>'Facilitators'] as $key=>$label)
        <div class="bg-white rounded-xl shadow p-5 text-center">
            <div class="text-3xl font-bold">{{ $stats[$key] }}</div>
            <div class="text-slate-400 text-sm">{{ $label }}</div>
        </div>
    @endforeach
</div>

<div class="grid md:grid-cols-2 gap-8">
    @foreach(['Elementary' => $elementaryTally, 'High School' => $highSchoolTally] as $levelLabel => $tally)
        <div>
            <h2 class="font-semibold mb-3">Top 5 — {{ $levelLabel }} Medal Tally</h2>
            <div class="bg-white rounded-xl shadow divide-y divide-slate-100">
                @forelse($tally as $i => $team)
                    <div class="flex flex-col gap-1 px-4 py-3 text-sm min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                        <span>{{ $i+1 }}. {{ $team->name }}</span>
                        <span class="whitespace-nowrap">🥇{{ $team->gold_count }} 🥈{{ $team->silver_count }} 🥉{{ $team->bronze_count }}</span>
                    </div>
                @empty
                    <p class="px-4 py-3 text-sm text-slate-400">No teams or medals yet.</p>
                @endforelse
            </div>
        </div>
    @endforeach
    
    <div>
        <h2 class="font-semibold mb-3">Recently Added Games</h2>
        <div class="bg-white rounded-xl shadow divide-y divide-slate-100">
            @foreach($recentGames as $game)
                <div class="px-4 py-3 text-sm flex items-center justify-between gap-2">
                    <span class="flex items-center gap-2">
                        {{ $game->sport->name }} — {{ $game->name }}
                        <x-level-badge :level="$game->level" />
                    </span>
                    <span class="text-slate-400">{{ ucfirst($game->status) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
