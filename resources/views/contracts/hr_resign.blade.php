<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request Re-sign — MasarHR</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 480px;
            width: 100%;
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .logo {
            max-width: 130px;
            margin-bottom: 18px;
        }
        h1 {
            color: #b7791f;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .info-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px 18px;
            text-align: left;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 24px;
            border: 1px solid #e9edf2;
        }
        .info-box .label {
            color: #64748b;
            font-weight: 500;
        }
        .info-box strong {
            color: #1e293b;
        }
        .warning-note {
            background: #fefce8;
            border-left: 4px solid #b7791f;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #78350f;
            margin-bottom: 24px;
            text-align: left;
        }
        .btn-submit {
            background: #b7791f;
            color: #ffffff;
            padding: 12px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: #9a651a;
        }
        .btn-submit:disabled {
            background: #d1b889;
            cursor: not-allowed;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
        .success-box {
            padding: 20px;
            background: #f0fdf4;
            border-radius: 10px;
            border: 1px solid #86efac;
        }
        .success-box h2 {
            color: #166534;
            margin: 0;
        }
        .success-box p {
            color: #14532d;
            margin: 8px 0 0 0;
        }
    </style>
</head>
<body>

<div class="container" id="app-container">
    <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo">

    <h1>Request Re-sign</h1>
    <p class="subtitle">Send a new signature link to the candidate</p>

    <div class="info-box">
        <div><span class="label">Candidate:</span> <strong>{{ $contract->offer->candidate->full_name }}</strong></div>
        <div><span class="label">Position:</span> {{ $contract->offer->jobPosting->requisition->job_title }}</div>
        <div><span class="label">Contract:</span> #{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div><span class="label">Status:</span> <span style="color: #b7791f; font-weight: 600;">Awaiting HR Action</span></div>
    </div>

    <div class="warning-note">
        ⚠️ This will send a new signature request to the candidate.
        The previous signature will be invalidated.
    </div>

    <button type="button" class="btn-submit" id="submit-btn">🔄 Confirm & Send</button>

    <p class="footer-note">The new link will be valid for 7 days.</p>
</div>

<script>
    (function() {
        const submitBtn = document.getElementById('submit-btn');

        submitBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to request a re-sign from the candidate?')) {
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Sending...';

            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === false) {
                    alert('❌ Error: ' + (data.message || 'Something went wrong.'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = '🔄 Confirm & Send';
                } else {
                    document.getElementById('app-container').innerHTML = `
                        <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo">
                        <div class="success-box">
                            <h2>✅ Request Sent</h2>
                            <p>The candidate has been notified to re-sign the contract.</p>
                        </div>
                        <p style="color:#64748b;font-size:13px;margin-top:16px;">
                            They will receive a new signature link via email.
                        </p>
                    `;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('❌ Connection error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = '🔄 Confirm & Send';
            });
        });
    })();
</script>

</body>
</html>
