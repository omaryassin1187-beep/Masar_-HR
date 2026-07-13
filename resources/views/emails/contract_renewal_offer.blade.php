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
    <h1 style="color: #ffffff; margin: 0;">Contract Renewal Offer</h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
    <p style="color: #334155;">Dear <strong>{{ $employeeName }}</strong>,</p>
    <p style="color: #334155;">We are pleased to offer you a contract renewal with the following terms:</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 180px;">Start Date</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $newStartDate }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">End Date</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $newEndDate }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Hourly Rate</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; color: #4A7C59; font-weight: bold;">${{ $newHourPrice }} / hour</td>
        </tr>
    </table>

    <p style="color: #64748b; font-size: 13px;">This offer is valid until: <strong>{{ $expiresAt }}</strong></p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 30px auto; text-align: center;">
        <tr>
            <td style="border-radius: 6px; background-color: #4A7C59;">
                <a href="{{ $acceptUrl }}" target="_blank"
                   style="background-color: #4A7C59; color: #ffffff; padding: 14px 28px;
                          text-decoration: none; border-radius: 6px; font-size: 16px;
                          font-weight: bold; display: inline-block; border: 1px solid #4A7C59;">
                    ✓ Accept Renewal
                </a>
            </td>
            <td style="width: 20px; font-size: 0; line-height: 0;">&nbsp;</td>
            <td style="border-radius: 6px; background-color: #dc2626;">
                <a href="{{ $rejectUrl }}" target="_blank"
                   style="background-color: #dc2626; color: #ffffff; padding: 14px 28px;
                          text-decoration: none; border-radius: 6px; font-size: 16px;
                          font-weight: bold; display: inline-block; border: 1px solid #dc2626;">
                    ✗ Decline Renewal
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © {{ date('Y') }} MasarHR. All rights reserved.
</p>

</body>
</html>
