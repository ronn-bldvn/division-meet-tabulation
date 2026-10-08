<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Division Sports Meet')</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="{{ asset('logos/divmeetlogo.ico') }}">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="mx-auto w-full max-w-6xl px-4 text-center">
            @php use App\Support\Level; @endphp
            {{-- LOGO + DIVISION MEET TITLE --}}
            <div class="flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">

                {{-- DIVISION MEET LOGO --}}
                {{-- <div class="h-20 w-20 flex-shrink-0 sm:h-28 sm:w-28">
                    <img src="{{ asset('logos/sjcseal.jpg') }}" alt="Division Meet Logo"
                        class="w-full h-full object-contain">
                </div> --}}

                {{-- TITLE --}}
                <div class="w-full text-center sm:w-auto">
                    <h1 class="text-2xl font-extrabold leading-tight text-green-800 sm:text-4xl md:text-5xl">
                        San Jose City Division Athletic Meet
                    </h1>

                    <h2 class="mt-1 text-2xl font-extrabold text-green-800 sm:text-3xl md:text-4xl">
                        Medal Tally
                    </h2>
                </div>
            </div>

            {{-- EVENT DATE --}}
            <p class="text-slate-500 mt-4">
                Date of the Division Meet:
                <span class="font-medium">
                    {{ $eventSetting?->event_date?->format('F j, Y') ?? 'To be announced' }}
                </span>
            </p>

            {{-- LAST UPDATED --}}
            <p class="text-sm text-slate-400 mt-1">
                Last updated:
                {{ $lastUpdated?->format('F j, Y g:i A') ?? 'No facilitator updates yet' }}
            </p>

            {{-- LEVEL --}}
            @if($level)
                <p class="mt-3">
                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-800
                             px-3 py-1 text-sm font-semibold">
                        {{ Level::label($level) }} Level
                    </span>
                </p>
            @endif

    </header>
    {{-- <header class="bg-slate-900 text-white">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4">
            <a href="{{ route('public.tally') }}" class="font-bold tracking-wide">Division Sports Meet</a>
            <a href="{{ route('public.tally') }}" class="text-sm hover:text-slate-300">Overall Tally</a>
        </div>
    </header> --}}
    <main class="mx-auto max-w-6xl px-4 py-6 sm:py-8">
        @yield('content')
    </main>
</body>
</html>
