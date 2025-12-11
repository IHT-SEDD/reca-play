<!DOCTYPE html>
<html>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background:#f7f7f7;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f7f7; padding: 40px 0;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius: 12px; padding: 40px;">


                <tr>
                    <td align="center" style="padding-bottom: 20px;">
                        <img src="{{ $message->embed(public_path('assets/img/logos/reca-black.png')) }}"
                             alt="Logo"
                             style="width: 120px; height:auto;">
                    </td>
                </tr>

                <tr>
                    <td align="center"
                        style="padding: 20px 0; font-size: 18px; font-weight: 600; line-height: 1.5;">
                        Please verify your email address.<br>
                        Click the button below to confirm it.
                    </td>
                </tr>


                <tr>
                    <td align="center" style="padding: 30px 0;">
                        <a href="{{ $url }}" target="_blank"
                            style="background-color:#6366f1;
                                   color:#ffffff;
                                   font-weight: 600;
                                   padding:12px 30px;
                                   text-decoration:none;
                                   border-radius:8px;
                                   display:inline-block;">
                            Verify Email
                        </a>
                    </td>
                </tr>


                <tr>
                    <td align="center"
                        style="padding-top: 20px;
                               font-size: 16px;
                               font-weight: 500;
                               color:#7d7d8c;
                               line-height: 1.5;">
                        If you did not create an account, you can ignore this email.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
