@props([
    'modal' => null,
    'label' => 'Close',
])

<button type="button"
    @click="$dispatch('close-modal', '{{ $modal }}')"
    {{ $attributes->merge([
        'class' => 'rounded-xl bg-vivaldi-red/20 text-vivaldi-red hover:bg-vivaldi-red hover:text-white text-sm p-3 font-medium inline-flex items-center justify-center gap-2'
    ]) }}>
    {{ $slot }}
</button>
