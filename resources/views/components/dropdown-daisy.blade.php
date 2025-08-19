@props([
'position' => 'dropdown-bottom dropdown-end',
'textSize' => 'text-xs',
'icon' => 'hard-drive'
])

<div x-data="{ open: false }" {{ $attributes->class("dropdown $position") }}>
    <div tabindex="0" role="button" @click="open = !open" @click.away="open = false"
        class="m-1 hover:text-hot-shot flex items-center gap-2 cursor-pointer font-medium {{ $textSize }}">
        <i data-lucide="{{ $icon }}" class="w-[15px] h-[15px]"></i>
        Masters
        <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
            class="w-[15px] h-[15px] transition-transform duration-300"></i>
    </div>
    <ul tabindex="0" x-show="open" x-transition
        class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
        {{ $slot }}
    </ul>
</div>