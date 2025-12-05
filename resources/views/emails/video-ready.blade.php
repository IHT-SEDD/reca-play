@component('mail::message')
<x-mail.mail-layout>
    <x-slot name="headerText">
        <tr>
            <td align="center" style="padding: 20px 0; font-size: 18px; font-weight: 600; line-height: 1.5;">
                Your video with name {{ $recording->video_name ?? ($video->video_filename ?? 'N/A') }} is ready to watch on RECA PLAY<br>
                Click the button below to visit it.
            </td>
        </tr>
    </x-slot>

    <x-slot name="button">
        <a href="{{ url('/my-recording') }}" target="_blank"
            style="background-color:#EC5228; color:#ffffff; font-weight: 500; padding:10px 25px; text-decoration:none; border-radius:8px; display:inline-block;">
            Open RECA PLAY
        </a>
    </x-slot>

    <x-slot name="footer">
        <tr>
            <td align="center"
                style="padding-top: 20px; font-size: 17px; font-weight: 500; color:#7d7d8c; line-height: 1.5;">
                Please immediately download your video, as it will be permanently deleted after 5 days. <br>
                Don't forget to check out our other features and services on RECA PLAY.<br>
                Thank you for using RECA PLAY!
            </td>
        </tr>
    </x-slot>
</x-mail.mail-layout>
@endcomponent