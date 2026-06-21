<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc;">

{{-- Header --}}
<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}"
        alt="MasarHR Logo"
        style="max-width: 150px; height: auto;">
</div>

<div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
    <h1 style="color: #ffffff; margin: 0;">Welcome to MasarHR!</h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">Your Account Has Been Created</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">

    <p style="color: #334155;">Dear <strong>{{ $userName }}</strong>,</p>

    <p style="color: #334155;">
        Congratulations! Your application has been accepted. Click the button below to set your password and access your account.
    </p>

    {{-- Credentials --}}
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 120px;">Email</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $email }}</td>
        </tr>
    </table>

    {{-- Set Password Button --}}
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $setPasswordUrl }}"
           target="_blank"
           style="background-color: #4A7C59; color: #ffffff; padding: 14px 32px;
                  text-decoration: none; border-radius: 6px; font-size: 16px;
                  font-weight: bold; display: inline-block;">
            Set Your Password
        </a>
    </div>

    <p style="color: #334155;">
        You will be required to upload your documents on your first login.
    </p>

    <p style="color: #334155;">
        Welcome to the team!
    </p>

    <p style="color: #334155;">
        Best regards,<br>
        <strong>MasarHR Team</strong>
    </p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © 2026 MasarHR. All rights reserved.
</p>

</body>
</html>
