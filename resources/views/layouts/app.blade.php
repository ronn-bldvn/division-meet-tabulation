<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Division Sports Meet — Tabulation')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="{{ asset('logos/divmeetlogo.ico') }}">
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <script>
      tailwind.config = { theme: { extend: { colors: { gold:'#d4af37', silver:'#c0c0c0', bronze:'#cd7f32' } } } }
    </script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">
    <nav class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <a href="{{ route('public.tally') }}" class="font-bold tracking-wide">🏆 Division Sports Meet</a>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <a href="{{ route('public.tally') }}" class="hover:text-slate-300">Public Tally</a>
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-300">Admin</a>
                        <a href="{{ route('admin.sports.index') }}" class="hover:text-slate-300">Sports</a>
                        <a href="{{ route('admin.teams.index') }}" class="hover:text-slate-300">Divisions</a>
                        <a href="{{ route('admin.games.index') }}" class="hover:text-slate-300">Games</a>
                        <a href="{{ route('admin.users.index') }}" class="hover:text-slate-300">Accounts</a>
                    @else
                        <a href="{{ route('facilitator.dashboard') }}" class="hover:text-slate-300">My Sport</a>
                    @endif
                    <span class="hidden text-slate-400 md:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-white">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6 sm:py-8">
        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-4 py-2 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-md bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
