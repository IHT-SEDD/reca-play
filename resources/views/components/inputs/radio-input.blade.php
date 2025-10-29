@props([
'disabled' => false,
'id' => '',
'value' => '',
'name' => '',
])

<div class="flex items-center mt-1.5">
    <input id="{{ $id }}" type="radio" value="{{ $value }}" name="{{ $name }}" @disabled($disabled) {{
        $attributes->merge([
    'class' => 'w-4.5 h-4.5 text-hot-shot bg-base-200/50 border-base-300 focus:ring-miami focus:ring-2'
    ]) }}>
    <label for="{{ $name }}" class="ms-2 text-sm font-medium text-after-midnight/70">{{ $slot }}</label>
</div>