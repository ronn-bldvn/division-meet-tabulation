@extends('layouts.app')
@section('title', 'Staff Login')
@section('content')
<div class="max-w-sm mx-auto bg-white rounded-xl shadow p-6 mt-10">
    <h1 class="text-xl font-bold mb-4">Staff Login</h1>
    <p class="text-sm text-slate-500 mb-4">For Sport Facilitators and the Super Admin. Public results don't require login.</p>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember"> Remember me
        </label>
        <button class="w-full bg-blue-600 hover:bg-blue-500 text-white rounded-lg py-2 font-medium">Log in</button>
    </form>
</div>
@endsection
