<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>
    <div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
    <h1 style="color: #ffffff; margin: 0;">Interview Invitation</h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Recruitment</p>
</div>

    <div style="background-color: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
        <p style="color: #334155;">Dear <strong>{{ $candidateName }}</strong>,</p>

        <p style="color: #334155;">Congratulations! We are pleased to inform you that you have been shortlisted for an interview.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 120px;">Position</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $jobTitle }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Date</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $scheduledAt }}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Location</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $location }}</td>
            </tr>
        </table>

        <p style="color: #334155;">Please confirm your attendance at your earliest convenience. If you have any questions, feel free to contact our HR department.</p>

        <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
    </div>

    <p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
        © 2026 MasarHR. All rights reserved.
    </p>

</body>
</html>
