@props([
'bg' => 'bg-after-midnight/90',
'hoverBg' => 'hover:bg-after-midnight',
'textColor' => 'text-base-100',
'btnId' => null,
'route' => route('google.login')
])

<a {{ $attributes->merge([
    'class' => "inline-flex items-center justify-center px-4 py-3
    {$bg} {$textColor} {$hoverBg}
    border-transparent border rounded-xl font-medium text-xs md:text-sm capitalize
    tracking-widest
    hover:bg-after-midnight
    focus:outline-none disabled:opacity-25 transition ease-in-out
    duration-150"
    ]) }} href="{{ $route }}">
    {{ $slot }}
</a>
