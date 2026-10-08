@extends('layouts.app')
@section('title', 'Manage Sports')
@section('content')
<h1 class="text-2xl font-bold mb-6">Manage Sports</h1>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <h2 class="font-semibold mb-3">Add Sport</h2>
    <form method="POST" action="{{ route('admin.sports.store') }}" class="flex flex-col gap-3 sm:flex-row">
        @csrf
        <input type="text" name="icon" placeholder="🏀" maxlength="10" class="w-full rounded-lg border-slate-300 text-sm sm:w-20">
        <input type="text" name="name" placeholder="Sport name" required class="w-full flex-1 rounded-lg border-slate-300 text-sm">
        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg px-4 py-2 text-sm font-medium">Add</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow divide-y divide-slate-100">
    @foreach($sports as $sport)
        <div class="p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('admin.sports.update', $sport) }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1">
                @csrf @method('PUT')
                <input type="text" name="icon" value="{{ $sport->icon }}" class="w-full rounded-lg border-slate-300 text-sm sm:w-16">
                <input type="text" name="name" value="{{ $sport->name }}" class="flex-1 rounded-lg border-slate-300 text-sm">
                <span class="whitespace-nowrap text-xs text-slate-400">{{ $sport->games_count }} games</span>
                <button class="text-blue-600 hover:underline text-sm">Save</button>
            </form>
            <form method="POST" action="{{ route('admin.sports.destroy', $sport) }}" onsubmit="return confirm('Delete this sport and all its games?')" class="sm:ml-3">
                @csrf @method('DELETE')
                <button class="text-red-500 hover:underline text-sm">Delete</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
