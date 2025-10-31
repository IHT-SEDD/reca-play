@props([
'value' => null,
'required' => false,
])


<label {{ $attributes->merge(['class' => 'block label-input flex lg:flex-row flex-col lg:items-center items-start
    justify-between gap-2']) }}>
    <span class="flex items-center gap-1">
        {{ $value ?? $slot }}
        @if ($required)
        <span class="text-vivaldi-red">*</span>
        @endif
    </span>

    @isset($extras)
    <span class="flex items-center flex-shrink-0">
        {{ $extras }}
    </span>
    @endisset
</label>