@props(['level'])

@php
    use App\Support\Level;

    $isElementary = $level === Level::ELEMENTARY;
    $palette = $isElementary
        ? 'bg-emerald-100 text-emerald-800 ring-emerald-200'
        : 'bg-indigo-100 text-indigo-800 ring-indigo-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$palette]) }}>
    {{ Level::label($level) }}
</span>
