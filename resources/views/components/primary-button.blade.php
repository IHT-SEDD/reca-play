<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-3
    bg-hot-shot/90 border
    border-transparent rounded-xl font-medium text-sm text-base-100 capitalize tracking-widest hover:bg-hot-shot
    focus:outline-none transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>