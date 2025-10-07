@props([
'value' => null,
'required' => false,
])


<label {{ $attributes->merge(['class' => 'block font-medium text-xs md:text-sm text-after-midnight flex items-center
    gap-1']) }}>
    {{ $value ?? $slot }}
    @if ($required)
    <span class="text-vivaldi-red">*</span>
    @endif
</label>