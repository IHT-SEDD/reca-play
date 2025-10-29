@props([
'active' => false,
])

@php
$classes = $active
? 'active text-hot-shot font-semibold transition duration-150 ease-in-out'
: 'transition duration-150 ease-in-out';
@endphp

<li>
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
</li>