<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MasarHR — HR Action Required</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8fafc; direction: ltr;">

    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 15px;">
        <img src="{{ $message->embed(public_path('images/logo.jpg')) }}"
             alt="MasarHR Logo" style="max-width: 150px; height: auto;">
    </div>

    <!-- Header -->
    <div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px;">HR Action Required</h1>
        <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Platform</p>
    </div>

    <!-- Body -->
    <div style="background-color: #ffffff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">

        <p style="color: #2d3748; font-size: 16px;">Hello <strong>{{ $hrName }}</strong>,</p>
        <p style="color: #2d3748; line-height: 1.5;">Candidate <strong>{{ $candidate }}</strong> has successfully signed the contract digitally.</p>
        <p style="color: #2d3748; line-height: 1.5;">The contract is now awaiting your review and final approval.</p>

        <!-- Info Table -->
        <div style="background-color: #f1f5f9; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #2d3748; width: 40%;">Candidate</td>
                    <td style="padding: 8px 0; color: #2d3748;"><strong>{{ $candidate }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #2d3748;">Action Type</td>
                    <td style="padding: 8px 0; color: #059669; font-weight: 600;">Contract Approval</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; color: #2d3748;">Document Status</td>
                    <td style="padding: 8px 0; color: #b7791f; font-weight: 600;">Awaiting HR Signature</td>
                </tr>
            </table>
        </div>

        <!-- Main Button -->
        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $signUrl }}" target="_blank"
               style="background-color: #4A7C59; color: #ffffff; padding: 14px 32px;
                      text-decoration: none; border-radius: 6px; font-size: 16px;
                      font-weight: bold; display: inline-block; box-shadow: 0 2px 4px rgba(74, 124, 89, 0.2);">
                🔎 Review & Sign Contract
            </a>
        </div>

        <!-- Alternative Actions -->
        <div style="border-top: 1px dashed #cbd5e1; padding-top: 25px; text-align: center; margin-top: 10px;">
            <p style="color: #64748b; font-size: 14px; font-weight: 600; margin-bottom: 20px;">
                Alternative Actions
            </p>

                <a href="{{ $resignUrl }}" target="_blank"
                   style="background-color: #daa549; color: #ffffff; padding: 12px 28px;
                          text-decoration: none; border-radius: 6px; font-size: 15px;
                          font-weight: bold; display: inline-block; margin: 5px;">
                    🔄 Request Re-sign
                </a>
            </div>
        </div>


        <p style="text-align: center; color: #64748b; font-size: 13px; margin-top: 25px;">
            ⚠️ This link is valid for 7 days.
        </p>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

        <p style="color: #2d3748; margin: 0;">Best regards,<br><strong style="color: #4A7C59;">MasarHR Team</strong></p>
    </div>

    <!-- Footer -->
    <p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} MasarHR. All rights reserved.
    </p>

</body>
</html>
