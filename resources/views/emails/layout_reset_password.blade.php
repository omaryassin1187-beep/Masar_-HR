<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Masar HR' }}</title>
</head>
<body style="margin:0;padding:40px 0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">

            <table width="650" cellpadding="0" cellspacing="0"
                   style="background:#ffffff;border-radius:10px;overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="padding:30px;text-align:center;border-bottom:4px solid #4A7C59;">

                        <img
                            src="{{ $message->embed(public_path('images/logo.jpg')) }}"
                            alt="Masar HR"
                            style="max-width:170px;">

                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:45px;">

                        @yield('content')

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
                            font-size:13px;
                            color:#777;
                        ">

                        © {{ date('Y') }} Masar HR System

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>