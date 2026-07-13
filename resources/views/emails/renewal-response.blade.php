<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MasarHR — Contract Renewal Response</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ asset('images/logo.jpg') }}" alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>

<div style="background-color: {{ $action === 'accept' ? '#4A7C59' : '#dc2626' }}; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
    <h1 style="color: #ffffff; margin: 0;">
        {{ $action === 'accept' ? '✅ Renewal Accepted' : '❌ Renewal Declined' }}
    </h1>
    <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
    <p style="color: #334155;">Hello <strong>{{ $renewal->user->full_name }}</strong>,</p>

    @if($action === 'accept')
        <p style="color: #334155;">You have successfully <strong>accepted</strong> the contract renewal offer.</p>
        <p style="color: #334155;">Your new contract will start on <strong>{{ $renewal->new_start_date->format('Y-m-d') }}</strong> and end on <strong>{{ $renewal->new_end_date->format('Y-m-d') }}</strong>.</p>
    @else
        <p style="color: #334155;">You have <strong>declined</strong> the contract renewal offer.</p>
        <p style="color: #334155;">Your current contract will remain active until <strong>{{ $renewal->contract->end_date->format('Y-m-d') }}</strong>.</p>
    @endif

    <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © {{ date('Y') }} MasarHR. All rights reserved.
</p>

</body>
</html>
