@props(['align' => 'right', 'columns' => 1])

@php
$alignmentClasses = match ($align) {
'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
'top' => 'origin-top',
default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};
@endphp

@php
$columnsClass = match ($columns) {
1 => 'grid-cols-1',
2 => 'grid-cols-2',
3 => 'grid-cols-3',
4 => 'grid-cols-4',
default => 'grid-cols-1',
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 w-48 min-w-max rounded-md shadow-lg {{ $alignmentClasses }}" style="display: none;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
            <div class="grid {{ $columnsClass }} gap-1 p-1">
                {{ $content }}
            </div>
        </div>
    </div>
</div>