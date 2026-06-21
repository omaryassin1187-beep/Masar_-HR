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
    <h1 style="color: #ffffff; margin: 0;">Job Offer</h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Recruitment</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">

    <p style="color: #334155;">Dear <strong>{{ $candidateName }}</strong>,</p>

    <p style="color: #334155;">
        Congratulations! We are pleased to extend you a formal job offer for the position of
        <strong>{{ $jobTitle }}</strong>.
    </p>

    {{-- Offer Details --}}
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 180px;">Position</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $jobTitle }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Start Date</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $startDate }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Hourly Rate</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">${{ $hourPrice }} / hour</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Working Hours / Day</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $workingHoursPerDay }} hours</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Weekend Days</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ implode(', ', $weekendDays) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Est. Monthly Salary</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; color: #4A7C59; font-weight: bold;">
                ${{ $estimatedMonthlySalary }}
            </td>
        </tr>
    </table>

    <p style="color: #334155;">
        Please review the offer details above and respond by clicking one of the buttons below.
        This offer is valid for <strong>4 days</strong> from the date of this email.
    </p>

    {{-- Action Buttons --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 30px auto; text-align: center;">
    <tr>
        <!-- Accept Button -->
        <td style="border-radius: 6px; background-color: #4A7C59;">
            <a href="{{ $acceptUrl }}"
               target="_blank"
               style="background-color: #4A7C59; color: #ffffff; padding: 14px 28px;
                      text-decoration: none; border-radius: 6px; font-size: 16px;
                      font-weight: bold; display: inline-block; border: 1px solid #4A7C59;">
                ✓ Accept Offer
            </a>
        </td>

        <!-- Spacer Column (قوة الفصل المضمونة في الإيميل) -->
        <td style="width: 20px; font-size: 0; line-height: 0;">
            &nbsp;
        </td>

        <!-- Decline Button -->
        <td style="border-radius: 6px; background-color: #dc2626;">
            <a href="{{ $rejectUrl }}"
               target="_blank"
               style="background-color: #dc2626; color: #ffffff; padding: 14px 28px;
                      text-decoration: none; border-radius: 6px; font-size: 16px;
                      font-weight: bold; display: inline-block; border: 1px solid #dc2626;">
                ✗ Decline Offer
            </a>
        </td>
    </tr>
</table>

    <p style="color: #64748b; font-size: 13px;">
        If the buttons above don't work, you can copy and paste the following links into your browser:<br>
        Accept: <span style="color: #4A7C59;">{{ $acceptUrl }}</span><br>
        Decline: <span style="color: #dc2626;">{{ $rejectUrl }}</span>
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
