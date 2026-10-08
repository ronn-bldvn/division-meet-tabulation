<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Overall Medal Tally</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('logos/divmeetlogo.ico') }}">
    {{--
    <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white min-h-screen text-slate-800">

    @php use App\Support\Level; @endphp

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

        {{-- ============================= --}}
        {{-- HEADER --}}
        {{-- ============================= --}}

        <div class="text-center mb-8">

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

            {{-- EVENT INFORMATION --}}
            <div class="w-full text-center mt-4">
                <p class="text-slate-500">
                    Date of the Division Meet:
                    <span class="font-medium">
                        {{ $eventSetting?->event_date?->format('F j, Y') ?? 'To be announced' }}
                    </span>
                </p>

                <p class="text-sm text-slate-400 mt-1">
                    Last updated:
                    {{ $lastUpdated?->format('F j, Y g:i A') ?? 'No facilitator updates yet' }}
                </p>
            </div>

            {{-- LEVEL --}}
            @if($level)
                <p class="mt-3">
                    <span class="inline-flex items-center rounded-full bg-green-100 text-green-800
                                 px-3 py-1 text-sm font-semibold">
                        {{ Level::label($level) }} Level
                    </span>
                </p>
            @endif

        </div>


        {{-- ============================= --}}
        {{-- TABS --}}
        {{-- ============================= --}}

        <div class="bg-yellow-400 p-2 rounded-t-lg">

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

                {{-- MEDAL TALLY TAB --}}
                <button type="button" onclick="showTab('medalTab', this)" class="tab-button bg-green-700 text-white
                           text-center font-medium py-3 rounded-lg
                           transition shadow-sm">

                    Medal Tally

                </button>


                {{-- EVENTS TAB --}}
                <button type="button" onclick="showTab('eventsTab', this)" class="tab-button bg-yellow-500 text-slate-900
                           text-center font-medium py-3 rounded-lg
                           transition">

                    Events

                </button>

            </div>

        </div>


        {{-- ====================================================== --}}
        {{-- MEDAL TALLY TAB --}}
        {{-- ====================================================== --}}

        <section id="medalTab">

            {{-- FILTERS: level + sport (both filter the tally server-side) --}}
            <form method="GET" action="{{ route('public.tally') }}"
                class="flex flex-wrap items-end justify-center gap-4 py-5">

                <div>
                    <label for="levelFilter"
                        class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Level
                    </label>

                    <select id="levelFilter" name="level" onchange="this.form.submit()" class="w-full sm:w-56 bg-white border border-slate-300
                               rounded-lg px-4 py-2 text-sm text-slate-700
                               shadow-sm focus:outline-none
                               focus:ring-2 focus:ring-green-600">

                        <option value="">All Levels</option>

                        @foreach(Level::ALL as $value => $label)
                            <option value="{{ $value }}" @selected($level === $value)>
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label for="sportFilter"
                        class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Sport
                    </label>

                    <select id="sportFilter" name="sport" onchange="this.form.submit()" class="w-full sm:w-56 bg-white border border-slate-300
                               rounded-lg px-4 py-2 text-sm text-slate-700
                               shadow-sm focus:outline-none
                               focus:ring-2 focus:ring-green-600">

                        <option value="">All Sports</option>

                        @foreach($sports as $sport)
                            <option value="{{ $sport->id }}" @selected($sportId === $sport->id)>
                                {{ $sport->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                @if($level || $sportId)
                    <a href="{{ route('public.tally') }}" class="text-sm text-green-700 hover:underline pb-2">
                        Reset
                    </a>
                @endif

            </form>


            {{-- MEDAL TABLE --}}
            <div class="border border-slate-300 overflow-x-auto">

                <table class="w-full min-w-[34rem] border-collapse">

                    <thead>

                        <tr class="bg-slate-200 text-slate-900">

                            <th class="border border-slate-300
                                       px-4 py-3 text-center
                                       font-bold">

                                UNIT

                            </th>

                            <th class="border border-slate-300
                                       px-4 py-3 text-center
                                       font-bold">

                                GOLD

                            </th>

                            <th class="border border-slate-300
                                       px-4 py-3 text-center
                                       font-bold">

                                SILVER

                            </th>

                            <th class="border border-slate-300
                                       px-4 py-3 text-center
                                       font-bold">

                                BRONZE

                            </th>

                            <th class="border border-slate-300
                                       px-4 py-3 text-center
                                       font-bold w-24">

                                TOTAL

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($tally as $i => $team)

                            <tr class="hover:bg-slate-50 transition">

                                {{-- ========================== --}}
                                {{-- UNIT --}}
                                {{-- ========================== --}}

                                <td class="border border-slate-300 px-4 py-3">

                                    <div class="flex items-center gap-4">

                                        {{-- TEAM LOGO --}}
                                        @if($team->logo)

                                            <div class="w-20 h-20 flex-shrink-0 rounded-full
                                                                        flex items-center justify-center
                                                                        overflow-hidden">

                                                @php
                                                    $logoUrl = $team->logo;

                                                    if (
                                                        !str_starts_with($logoUrl, 'http://') &&
                                                        !str_starts_with($logoUrl, 'https://') &&
                                                        !str_starts_with($logoUrl, '/storage/')
                                                    ) {
                                                        $logoUrl = Storage::url($logoUrl);
                                                    }
                                                @endphp

                                                <img src="{{ $logoUrl }}" alt="{{ $team->name }}"
                                                    class="w-full h-full rounded-full object-cover">

                                            </div>

                                        @else

                                            {{-- ONLY USED IF NO LOGO EXISTS --}}
                                            <div class="w-20 h-20 flex-shrink-0 rounded-full
                                                                       flex items-center justify-center
                                                                       text-white font-bold text-lg"
                                                style="background-color: {{ $team->color ?? '#64748b' }}">

                                                {{ $team->code }}

                                            </div>

                                        @endif


                                        {{-- TEAM NAME --}}
                                        <div>

                                            <div class="font-bold text-slate-700">
                                                {{ $team->name }}
                                            </div>

                                            <div class="text-xs text-slate-400">
                                                {{ $team->code }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                {{-- GOLD --}}
                                <td class="border border-slate-300
                                                   px-4 py-3 text-center">

                                    @if($team->gold_count > 0)

                                        <span class="font-bold text-lg text-yellow-600">
                                            {{ $team->gold_count }}
                                        </span>

                                    @else

                                        <span class="text-slate-300">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- SILVER --}}
                                <td class="border border-slate-300
                                                   px-4 py-3 text-center">

                                    @if($team->silver_count > 0)

                                        <span class="font-bold text-lg text-slate-500">
                                            {{ $team->silver_count }}
                                        </span>

                                    @else

                                        <span class="text-slate-300">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- BRONZE --}}
                                <td class="border border-slate-300
                                                   px-4 py-3 text-center">

                                    @if($team->bronze_count > 0)

                                        <span class="font-bold text-lg text-amber-700">
                                            {{ $team->bronze_count }}
                                        </span>

                                    @else

                                        <span class="text-slate-300">
                                            —
                                        </span>

                                    @endif

                                </td>


                                {{-- TOTAL --}}
                                <td class="border border-slate-300
                                                   px-4 py-3 text-center">

                                    <span class="font-bold text-lg">
                                        {{ $team->total_medals }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="border border-slate-300
                                                   px-4 py-12 text-center
                                                   text-slate-400">

                                    No medals awarded yet.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- ENTRIES --}}
            <div class="text-sm text-slate-500 mt-2">

                Showing {{ $tally->count() }} entries

            </div>

        </section>


        {{-- ====================================================== --}}
        {{-- EVENTS TAB --}}
        {{-- ====================================================== --}}

        <section id="eventsTab" class="hidden">

            <div class="py-5">

                <h2 class="text-xl font-bold mb-4">
                    Events
                </h2>


                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">

                    @forelse($sports as $sport)

                        <a href="{{ route('public.sport', array_merge(['sport' => $sport], $level ? ['level' => $level] : [])) }}"
                            class="bg-white border border-slate-200
                                          rounded-xl shadow-sm p-5
                                          hover:shadow-md
                                          hover:border-green-300
                                          transition">

                            <div class="w-16 h-16 mb-3 mx-auto">
                                <img src="{{ asset('logos/sjclogo.png') }}" alt="Division Meet Logo"
                                    class="w-full h-full object-contain">
                            </div>

                            <div class="font-semibold">
                                {{ $sport->name }}
                            </div>

                            <div class="text-sm text-slate-400 mt-1">
                                {{ $sport->games_count }} game(s)
                            </div>

                        </a>

                    @empty

                        <p class="text-slate-400">
                            No events available.
                        </p>

                    @endforelse

                </div>

            </div>

        </section>

    </main>


    {{-- ============================= --}}
    {{-- TAB JAVASCRIPT --}}
    {{-- ============================= --}}

    <script>

        function showTab(tabId, button) {

            // Hide both sections
            document.getElementById('medalTab').classList.add('hidden');
            document.getElementById('eventsTab').classList.add('hidden');

            // Show selected section
            document.getElementById(tabId).classList.remove('hidden');


            // Reset all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {

                btn.classList.remove(
                    'bg-green-700',
                    'text-white'
                );

                btn.classList.add(
                    'bg-yellow-500',
                    'text-slate-900'
                );

            });


            // Activate selected button
            button.classList.remove(
                'bg-yellow-500',
                'text-slate-900'
            );

            button.classList.add(
                'bg-green-700',
                'text-white'
            );

        }

    </script>

</body>

</html>
