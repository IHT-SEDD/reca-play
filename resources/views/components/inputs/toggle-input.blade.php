@props(['disabled' => false, 'checked' => false])

<label class="relative inline-flex items-center cursor-pointer mt-2">
    <input type="hidden" name="{{ $attributes['name'] }}" value="0">
    <input type="checkbox" class="sr-only peer" value="1" @disabled($disabled) @checked($checked) {{ $attributes }}>
    <div class="w-11 h-6 bg-base-200 rounded-full peer peer-checked:bg-hot-shot
                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                after:bg-base-100 after:border-base-300 after:border after:rounded-full
                after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
    </div>
</label>
