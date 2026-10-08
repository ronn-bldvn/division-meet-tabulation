@extends('layouts.app')
@section('title', 'Manage Accounts')
@section('content')
<h1 class="text-2xl font-bold mb-6">Manage Staff Accounts</h1>

<div class="bg-white rounded-xl shadow p-5 mb-8">
    <h2 class="font-semibold mb-3">Add Account</h2>
    <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
        @csrf
        <input type="text" name="name" placeholder="Full name" required class="rounded-lg border-slate-300 text-sm">
        <input type="email" name="email" placeholder="Email" required class="rounded-lg border-slate-300 text-sm">
        <input type="password" name="password" placeholder="Password" required class="rounded-lg border-slate-300 text-sm">
        <select name="role" id="role-select" class="rounded-lg border-slate-300 text-sm" onchange="document.getElementById('sport-select').classList.toggle('hidden', this.value !== 'facilitator')">
            <option value="facilitator">Sport Facilitator</option>
            <option value="super_admin">Super Admin</option>
        </select>
        <select name="sport_id" id="sport-select" class="rounded-lg border-slate-300 text-sm">
            <option value="">Assign sport</option>
            @foreach($sports as $sport)<option value="{{ $sport->id }}">{{ $sport->name }}</option>@endforeach
        </select>
        <button class="bg-blue-600 hover:bg-blue-500 text-white rounded-lg py-2 text-sm font-medium sm:col-span-2 md:col-span-1">Add</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow divide-y divide-slate-100">
    @foreach($users as $user)
        <div class="p-4 flex items-center justify-between">
            <div>
                <div class="font-medium">{{ $user->name }} <span class="text-xs text-slate-400">{{ $user->email }}</span></div>
                <div class="text-xs text-slate-400">
                    {{ $user->role === 'super_admin' ? 'Super Admin' : 'Facilitator — '.optional($user->sport)->name }}
                </div>
            </div>
            <div class="flex items-center gap-3">
                <details class="relative">
                    <summary class="cursor-pointer text-blue-600 hover:underline text-sm">Edit</summary>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="absolute right-0 z-10 mt-2 grid w-72 gap-3 rounded-xl border bg-white p-4 shadow-lg">
                        @csrf @method('PUT')
                        <label class="text-xs font-medium">Full name
                            <input type="text" name="name" value="{{ $user->name }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Email
                            <input type="email" name="email" value="{{ $user->email }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">New password (optional)
                            <input type="password" name="password" minlength="6" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                        </label>
                        <label class="text-xs font-medium">Role
                            <select name="role" required class="mt-1 w-full rounded-lg border-slate-300 text-sm" onchange="this.form.elements.sport_id.disabled = this.value !== 'facilitator'">
                                <option value="facilitator" @selected($user->role === 'facilitator')>Sport Facilitator</option>
                                <option value="super_admin" @selected($user->role === 'super_admin')>Super Admin</option>
                            </select>
                        </label>
                        <label class="text-xs font-medium">Assigned sport
                            <select name="sport_id" @disabled($user->role !== 'facilitator') class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                                <option value="">Assign sport</option>
                                @foreach($sports as $sport)
                                    <option value="{{ $sport->id }}" @selected($user->sport_id === $sport->id)>{{ $sport->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button class="rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-500">Save changes</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Remove this account?')">
                    @csrf @method('DELETE')
                    <button class="text-red-500 hover:underline text-sm">Delete</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
