<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>

<div style="background-color: #f59e0b; padding: 20px; border-radius: 8px 8px 0 0;">
    <h1 style="color: #ffffff; margin: 0;">Contracts Expiring Soon</h1>
    <p style="color: #fef3c7; margin: 5px 0 0 0;">MasarHR</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
    <p style="color: #334155;">Hello <strong>{{ $hrName }}</strong>,</p>
    <p style="color: #334155;">The following contracts will expire within 30 days and need review:</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr style="background-color: #f1f5f9;">
            <th style="padding: 10px; border: 1px solid #e2e8f0; text-align: left;">Employee</th>
            <th style="padding: 10px; border: 1px solid #e2e8f0; text-align: left;">Department</th>
            <th style="padding: 10px; border: 1px solid #e2e8f0; text-align: left;">Expires On</th>
        </tr>
        @foreach($contracts as $contract)
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $contract->user->full_name }}</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $contract->user->department->name ?? '—' }}</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $contract->end_date->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </table>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/contracts/expiring" target="_blank"
           style="background-color: #4A7C59; color: #ffffff; padding: 14px 32px;
                  text-decoration: none; border-radius: 6px; font-size: 16px;
                  font-weight: bold; display: inline-block;">
            View Expiring Contracts
        </a>
    </div>

    <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © {{ date('Y') }} MasarHR. All rights reserved.
</p>

</body>
</html>
