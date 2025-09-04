@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
'class' => 'border-base-300 py-3 focus:border-2
focus:border-miami transition-colors
focus:ring-0 focus:outline-none rounded-lg peer appearance-none placeholder:text-xs placeholder:text-base-300'
]) }}
>