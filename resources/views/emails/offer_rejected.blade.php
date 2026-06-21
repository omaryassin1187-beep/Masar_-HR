<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}" alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>

<div style="background-color: #dc2626; padding: 20px; border-radius: 8px 8px 0 0;">
    <h1 style="color: #ffffff; margin: 0;">Offer Declined</h1>
    <p style="color: #f0e0e0; margin: 5px 0 0 0;">MasarHR Recruitment</p>
</div>

<div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
    <p style="color: #334155;">Hello <strong>{{ $hrName }}</strong>,</p>
    <p style="color: #334155;"><strong>{{ $candidateName }}</strong> has declined the job offer for: <strong>{{ $jobTitle }}</strong></p>
    @if($nextCandidate)
<p style="color: #334155;">The next ranked candidate is <strong>{{ $nextCandidate->full_name }}</strong>.</p>
@endif
<p style="color: #334155;">Please review and send an offer to the next candidate.</p>
    <p style="color: #334155;">Best regards,<br><strong>MasarHR Team</strong></p>
</div>

<p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
    © {{ date('Y') }} MasarHR. All rights reserved.
</p>

</body>
</html>
