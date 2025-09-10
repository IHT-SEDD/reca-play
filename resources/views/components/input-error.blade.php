<div {{ $attributes->merge(['class' => 'p-1 mt-1 text-vivaldi-red flex justify-start items-center gap-1 hidden']) }}
    id="{{ $id ?? 'input-error' }}">
    <i data-lucide="circle-x" class="w-3 h-3"></i>
    <p class="text-xs font-semibold">{{ $slot }}</p>
</div>