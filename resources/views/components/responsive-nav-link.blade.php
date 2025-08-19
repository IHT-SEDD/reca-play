@props(['active'])

@php
$classes = ($active ?? false)
? 'block w-full ps-3 pe-4 py-2 border-l-4 border-hot-shot text-start tracking-wide capitalize text-hot-shot font-medium
transition
duration-150 ease-in-out'
: 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start tracking-wide capitalize text-after-midnight
font-medium
hover:text-hot-shot hover:border-hot-shot transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>