@props([
'disabled' => false,
'info' => '',
'name' => '',
'id' => ''
])

<div class="mt-3">
    <input type="file" id="{{ $id }}" name="{{ $name }}" accept="image/*" class="hidden"
        onchange="document.getElementById('{{ $id }}Label').innerText = this.files.length ? this.files[0].name : 'No file chosen'">

    <label for="{{ $id }}"
        class="cursor-pointer p-2 bg-hot-shot/80 text-white rounded-lg shadow hover:bg-hot-shot transition text-sm">
        Choose File
    </label>

    <span id="{{ $id }}Label" class="ml-3 text-carbon text-sm">No file chosen</span>
    <p class="mt-3 text-xs text-carbon">
        {{ $slot }}
    </p>
</div>
