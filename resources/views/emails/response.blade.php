<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MasarHR — Offer Response</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 500px; margin: 80px auto; padding: 20px; text-align: center;">

    <div style="background-color: {{ $action === 'accept' ? '#4A7C59' : '#dc2626' }};
                padding: 20px; border-radius: 8px 8px 0 0;">
        <h1 style="color: #ffffff; margin: 0;">
            {{ $action === 'accept' ? '✓ Offer Accepted' : '✗ Offer Declined' }}
        </h1>
    </div>

    <div style="background-color: #f8fafc; padding: 30px;
                border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
        <p style="color: #334155; font-size: 16px; line-height: 1.6;">
            {{ $message }}
        </p>
        <p style="color: #94a3b8; font-size: 13px; margin-top: 20px;">
            © 2026 MasarHR. All rights reserved.
        </p>
    </div>

</body>
</html>
