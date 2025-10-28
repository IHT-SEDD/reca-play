@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
'class' => 'timepicker bg-white dark:bg-transparent
border border-base-200/90 dark:border-base-200/50
py-2.5 px-3
focus:border-miami dark:focus:border-hot-shot
transition-colors duration-200
focus:shadow-md focus:outline-none
rounded-xl
placeholder:text-xs placeholder:text-base-300 dark:placeholder:text-base-200/80 shadow-sm'
]) }}
>