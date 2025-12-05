@component('mail::message')
<x-mail.mail-layout>
    <x-slot name="headerText">
        <tr>
            <td align="center" style="padding: 20px 0; font-size: 18px; font-weight: 600; line-height: 1.5;">
                We received a request to reset your password.<br>
                Click the button below to reset it.
            </td>
        </tr>
    </x-slot>

    <x-slot name="button">
        <a href="{{ $url }}" target="_blank"
            style="background-color:#EC5228; color:#ffffff; font-weight: 500; padding:10px 25px; text-decoration:none; border-radius:8px; display:inline-block;">
            Reset Password
        </a>
    </x-slot>

    <x-slot name="footer">
        <tr>
            <td align="center"
                style="padding-top: 20px; font-size: 17px; font-weight: 500; color:#7d7d8c; line-height: 1.5;">
                Didn’t ask for a new password? You can ignore this email.
            </td>
        </tr>
    </x-slot>
</x-mail.mail-layout>
@endcomponent