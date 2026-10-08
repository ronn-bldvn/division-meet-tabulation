@extends('layouts.app')
@section('title', 'Manage Divisions')
@section('content')
<h1 class="text-2xl font-bold mb-6">Manage Divisions / Teams</h1>
<p class="text-sm text-slate-500 mb-4">Team code determines level filtering: use <code>elemteam1</code>, <code>elemteam2</code>, etc. for Elementary and <code>hsteam1</code>, <code>hsteam2</code>, etc. for High School.</p>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <h2 class="font-semibold mb-3">Add Division</h2>
    <form method="POST" action="{{ route('admin.teams.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
        @csrf
        <input type="text" name="name" placeholder="Division name" required class="rounded-lg border-slate-300 text-sm sm:col-span-2">
        <input type="text" name="code" placeholder="elemteam1 / hsteam1" maxlength="10" required class="rounded-lg border-slate-300 text-sm">
        <input type="color" name="color" value="#2563eb" class="rounded-lg border-slate-300 h-10 w-full">
        <input type="file" name="logo" accept="image/*" class="text-xs">
        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg py-2 text-sm font-medium">Add</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow divide-y divide-slate-100">
    @foreach($teams as $team)
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full inline-block" style="background: {{ $team->color }}"></span>
                @if($team->logo)<img src="{{ asset('storage/'.$team->logo) }}" class="w-8 h-8 rounded-full object-cover">@endif
                <span class="font-medium">{{ $team->name }}</span>
                <span class="text-xs text-slate-400">{{ $team->code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <details class="relative">
                    <summary class="cursor-pointer text-blue-600 hover:underline text-sm">Edit</summary>
                    <form method="POST" action="{{ route('admin.teams.update', $team) }}" enctype="multipart/form-data" class="absolute right-0 z-10 mt-2 grid w-72 gap-3 rounded-xl border bg-white p-4 shadow-lg">
                        @csrf @method('PUT')
                        <label class="text-xs font-medium">Division name
                            <input type="text" name="name" value="{{ $team->name }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Code
                            <input type="text" name="code" value="{{ $team->code }}" maxlength="10" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Color
                            <input type="color" name="color" value="{{ $team->color }}" required class="mt-1 h-10 w-full rounded-lg border-slate-300">
                        </label>
                        <label class="text-xs font-medium">Logo (optional)
                            <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-xs">
                        </label>
                        @if($team->logo)
                            <span class="text-xs text-slate-400">Leave the logo blank to keep the current image.</span>
                        @endif
                        <button class="rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-500">Save changes</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Delete this division?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:underline text-sm">Delete</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
