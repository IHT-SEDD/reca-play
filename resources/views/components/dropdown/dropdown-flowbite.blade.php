@props([
'trigger' => 'Dropdown',
'iconTrigger' => 'hard-drive',
])

<div x-data="{ open: false }" class="relative inline-block text-left">
    <!-- Trigger button -->
    <button @click="open = !open" :class="[
        'dw-button',
        open 
            ? 'text-dw-button-active' 
            : 'text-dw-button-default'
    ]" type="button">
        @if ($iconTrigger)
        <i data-lucide="{{ $iconTrigger }}" class="w-4 h-4 me-2"></i>
        @endif
        {{ $trigger }}
        <i data-lucide="chevron-down" :class="{ 'rotate-180': open }"
            class="w-3.5 h-3.5 transition-transform duration-300 ms-1"></i>
    </button>

    <!-- Dropdown menu -->
    <div id="dropdownDivider" x-show="open" x-cloak @click.outside="open = false" x-transition class="dw-menu">
        <!-- Slot menu -->
        <ul class="flex flex-col justify-center items-start text-dw-menu-default p-2">
            {{ $slot }}
        </ul>
    </div>
</div>