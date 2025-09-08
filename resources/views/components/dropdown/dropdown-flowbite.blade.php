@props([
'trigger' => 'Dropdown',
'iconTrigger' => 'hard-drive',
'cols' => 2,
])

<div x-data="{ open: false }" class="relative inline-block text-left">
    <!-- Trigger button -->
    <button @click="open = !open" id="dropdownDividerButton" :class="open 
            ? 'bg-white-owl text-hot-shot' 
            : 'text-after-midnight hover:bg-white-owl hover:text-hot-shot'"
        class="focus:ring-0 focus:outline-none font-medium rounded-xl text-sm px-5 py-2.5 text-center inline-flex items-center transition-colors"
        type="button">
        <i data-lucide="{{ $iconTrigger }}" class="w-4 h-4 me-2"></i>
        {{ $trigger }}
        <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
            class="w-3.5 h-3.5 transition-transform duration-300 ms-1"></i>
    </button>

    <!-- Dropdown menu -->
    <div id="dropdownDivider" x-show="open" @click.outside="open = false" x-transition
        class="absolute right-0 z-10 mt-3 origin-top-right rounded-lg w-auto min-w-44 bg-white shadow-sm divide-y divide-black border border-white-edgar">
        <!-- Slot menu -->
        <ul
            class="grid gap-3 text-[13px] text-carbon font-medium p-2 [grid-template-columns:repeat({{ $cols }},minmax(150px,1fr))]">
            {{ $slot }}
        </ul>
    </div>
</div>