<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
                    <td style="padding:50px;">

                        <h2 style="margin-top:0;color:#2c3e50;text-align:center;">
                            Reset Your Password
                        </h2>

                        <p style="color:#666;text-align:center;line-height:1.8;margin-bottom:35px;">
                            Please enter your new password below.
                        </p>

                        @if ($errors->any())
                            <div style="
                                background:#fdecea;
                                color:#c62828;
                                padding:15px;
                                border-radius:6px;
                                margin-bottom:25px;
                            ">
                                <ul style="margin:0;padding-left:20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.reset') }}">

                            @csrf

                            <input
                                type="hidden"
                                name="token"
                                value="{{ $token }}">

                            <input
                                type="hidden"
                                name="email"
                                value="{{ $email }}">

                            <label style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                required
                                style="
                                    width:100%;
                                    padding:12px;
                                    border:1px solid #ddd;
                                    border-radius:6px;
                                    margin-bottom:25px;
                                    box-sizing:border-box;
                                ">

                            <label style="display:block;margin-bottom:8px;font-weight:bold;color:#333;">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                required
                                style="
                                    width:100%;
                                    padding:12px;
                                    border:1px solid #ddd;
                                    border-radius:6px;
                                    margin-bottom:35px;
                                    box-sizing:border-box;
                                ">

                            <button
                                type="submit"
                                style="
                                    width:100%;
                                    padding:14px;
                                    background:#4A7C59;
                                    color:white;
                                    border:none;
                                    border-radius:6px;
                                    font-size:16px;
                                    font-weight:bold;
                                    cursor:pointer;
                                ">

                                Reset Password

                            </button>

                        </form>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="
                        background:#f8f8f8;
                        border-top:1px solid #e5e5e5;
                        text-align:center;
                        padding:25px;
                        color:#777;
                        font-size:13px;">

                        © {{ date('Y') }} Masar HR System. All rights reserved.

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>