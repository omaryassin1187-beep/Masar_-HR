<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successfully</title>
</head>

<body style="margin:0;padding:40px 0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">

            <table width="650" cellpadding="0" cellspacing="0"
                   style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,.08);">

                {{-- Header --}}
                <tr>
                    <td style="padding:30px;text-align:center;border-bottom:4px solid #4A7C59;">

                        <img
                            src="{{ asset('images/logo.jpg') }}"
                            alt="Masar HR"
                            style="max-width:170px;">

                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:60px 50px;text-align:center;">

                        <div
                            style="
                                width:90px;
                                height:90px;
                                margin:0 auto 30px;
                                border-radius:50%;
                                background:#4A7C59;
                                color:#ffffff;
                                font-size:48px;
                                font-weight:bold;
                                line-height:90px;
                            ">
                            ✓
                        </div>

                        <h2
                            style="
                                margin:0 0 20px;
                                color:#2c3e50;
                                font-size:30px;
                            ">
                            Password Reset Successfully
                        </h2>

                        <p
                            style="
                                margin:0;
                                color:#555;
                                font-size:16px;
                                line-height:1.8;
                            ">
                            Your password has been changed successfully.
                        </p>

                        <p
                            style="
                                margin:12px 0 0;
                                color:#555;
                                font-size:16px;
                                line-height:1.8;
                            ">
                            You can now close this page and sign in to the
                            <strong>Masar HR</strong> application using your new password.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td
                        style="
                            background:#f8f8f8;
                            border-top:1px solid #e5e5e5;
                            text-align:center;
                            padding:25px;
                            color:#777;
                            font-size:13px;
                        ">

                        © {{ date('Y') }} Masar HR System. All rights reserved.

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>