@props([
    'messages' => [],
    'id' => null,
])

@if(!empty($messages))
    <div {{ $attributes->merge(['class' => 'p-1 mt-1 text-vivaldi-red flex justify-start items-start gap-1']) }}
         id="{{ $id ?? 'input-error' }}">

        <i data-lucide="circle-x" class="w-3 h-3 mt-1"></i>

        <div class="flex flex-col gap-0.5">
            @foreach ((array) $messages as $message)
                <p class="text-xs font-semibold">{{ $message }}</p>
            @endforeach
        </div>

    </div>
@endif
