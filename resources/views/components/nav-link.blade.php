@props(['active'])

@php
$classes = ($active ?? false)
? 'inline-flex items-center px-4 py-3 text-nav-active transition duration-150 ease-in-out'
: 'inline-flex items-center px-4 py-3 text-nav-default transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>