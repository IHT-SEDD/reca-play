@php
    $path = public_path('assets/img/logos/reca-black.png');
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = base64_encode(file_get_contents($path));
    $src = 'data:image/' . $type . ';base64,' . $data;
@endphp

@component('mail::message')

<div style="text-align:center; margin-bottom:20px;">
    <img src="{{ $src }}" width="70" alt="Logo">
</div>

# Reset Your Password

You requested a password reset.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
Reset Password
@endcomponent

If you didn't request this, ignore this email.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
