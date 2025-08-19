@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-after-midnight']) }}>
    {{ $value ?? $slot }}
</label>