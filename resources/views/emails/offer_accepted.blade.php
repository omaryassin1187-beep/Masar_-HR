<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>

<div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
    <h1 style="color: #ffffff; margin: 0;">Offer Accepted</h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Recruitment</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
    <p style="color: #334155;">Hello <strong>{{ $hrName }}</strong>,</p>
    <p style="color: #334155;"><strong>{{ $candidateName }}</strong> has accepted the job offer for: <strong>{{ $jobTitle }}</strong></p>
    <p style="color: #334155;">You can now proceed with the onboarding process.</p>

    <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © 2026 MasarHR. All rights reserved.
</p>

</body>
</html>
