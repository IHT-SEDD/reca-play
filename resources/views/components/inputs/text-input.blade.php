@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
'class' => '
bg-white dark:bg-transparent
border-base-300 dark:border-base-200/50
py-2.5
focus:border-1.5 focus:border-miami dark:focus:border-hot-shot
transition-colors
focus:ring-0 focus:outline-none
rounded-xl
peer appearance-none
placeholder:text-xs placeholder:tracking-wider placeholder:text-base-300 dark:placeholder:text-base-200/80
'
]) }}
>