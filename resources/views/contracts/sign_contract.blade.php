<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MasarHR — Sign Your Contract</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc; direction: ltr;">

<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ $message->embed(public_path('images/logo.jpg')) }}"
        alt="MasarHR Logo" style="max-width: 150px; height: auto;">
</div>

    <!-- Header -->
    <div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Sign Your Contract</h1>
        <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Platform</p>
    </div>

    <!-- Body -->
    <div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
        <p style="color: #2d3748; font-size: 16px;">Hello <strong>{{ $candidateName }}</strong>,</p>
        <p style="color: #2d3748; line-height: 1.5;">Thank you for accepting the job offer. We are excited to welcome you to our team.</p>
        <p style="color: #2d3748; line-height: 1.5;">Please review the attached contract below, then proceed to sign it using the link.</p>

        <!-- البلوك البصري للملف المرفق (تمت إضافته هنا قبل الزر) -->
        <div style="background-color: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 15px; margin: 25px 0; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 20px; margin-right: 10px;">📄</span>
            <span style="color: #334155; font-size: 14px; font-weight: 500;">
                Attached: <strong>contract_{{ $offerId ?? 'draft' }}.pdf</strong> (Scroll down to view/download)
            </span>
        </div>

        <!-- Button -->
        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $signedUrl }}" target="_blank"
               style="background-color: #4A7C59; color: #ffffff; padding: 14px 32px;
                      text-decoration: none; border-radius: 6px; font-size: 16px;
                      font-weight: bold; display: inline-block; box-shadow: 0 2px 4px rgba(74, 124, 89, 0.2);">
                Sign Contract
            </a>
        </div>

        <p style="text-align: center; color: #64748b; font-size: 13px; margin-top: 15px;">This link is valid for 7 days.</p>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

        <p style="color: #2d3748; margin: 0;">Best regards,<br><strong style="color: #4A7C59;">MasarHR Team</strong></p>
    </div>

    <!-- Footer -->
    <p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} MasarHR. All rights reserved.
    </p>

</body>
</html>
