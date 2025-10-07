@props(['active'])

@php
$classes = ($active ?? false)
? 'inline-flex items-center text-sm px-5 py-2.5 font-medium leading-5 tracking-wide text-hot-shot transition
duration-150
ease-in-out'
: 'inline-flex items-center text-sm px-5 py-2.5 font-medium leading-5 tracking-wide text-after-midnight dark:text-white
hover:text-hot-shot dark:hover:text-hot-shot transition
duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>