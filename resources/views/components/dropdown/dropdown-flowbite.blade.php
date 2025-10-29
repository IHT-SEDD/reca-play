@props([
'trigger' => 'Dropdown',
'iconTrigger' => 'hard-drive',
])

<div x-data="{ open: false }" class="relative inline-block text-left">
    <!-- Trigger button -->
    <button @click="open = !open" :class="[
        'focus:ring-0 focus:outline-none rounded-xl px-5 py-2.5 text-center inline-flex items-center transition-colors',
        open 
            ? 'text-dw-button-active' 
            : 'text-dw-button-default'
    ]" type="button">
        <i data-lucide="{{ $iconTrigger }}" class="w-4 h-4 me-2"></i>
        {{ $trigger }}
        <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
            class="w-3.5 h-3.5 transition-transform duration-300 ms-1"></i>
    </button>

    <!-- Dropdown menu -->
    <div id="dropdownDivider" x-show="open" x-cloak @click.outside="open = false" x-transition
        class="absolute right-0 z-10 mt-3 origin-top-right rounded-lg w-full min-w-fit bg-white shadow-sm divide-y divide-eerie-black border border-base-200">
        <!-- Slot menu -->
        <ul class="flex flex-col justify-center items-start text-dw-menu-default p-2">
            {{ $slot }}
        </ul>
    </div>
</div>