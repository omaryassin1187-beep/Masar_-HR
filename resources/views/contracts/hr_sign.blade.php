<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Contract — MasarHR</title>
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
            max-width: 520px;
            width: 100%;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .logo {
            max-width: 130px;
            margin-bottom: 18px;
        }
        h1 {
            color: #1a4a3a;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px 0;
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
            margin-bottom: 22px;
            border: 1px solid #e9edf2;
        }
        .info-box strong {
            color: #1e293b;
        }
        .info-box .label {
            color: #64748b;
            font-weight: 500;
        }
        .canvas-wrapper {
            width: 100%;
            height: 200px;
            position: relative;
            margin-bottom: 12px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #4A7C59;
            background: #ffffff;
        }
        canvas {
            display: block;
            width: 100%;
            height: 100%;
            cursor: crosshair;
            background: #ffffff;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .btn {
            padding: 10px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-clear {
            background: #e9edf2;
            color: #334155;
        }
        .btn-clear:hover {
            background: #d1d9e6;
        }
        .btn-submit {
            background: #4A7C59;
            color: #ffffff;
        }
        .btn-submit:hover {
            background: #3b6347;
        }
        .btn-submit:disabled {
            background: #a3b899;
            cursor: not-allowed;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="container">
    <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo">

    <h1>Sign Contract</h1>
    <p class="subtitle">HR Review & Approval</p>

    <div class="info-box">
        <div><span class="label">Employee:</span> <strong>{{ $contract->offer->candidate->full_name }}</strong></div>
        <div><span class="label">Position:</span> {{ $contract->offer->jobPosting->requisition->job_title }}</div>
        <div><span class="label">Start Date:</span> {{ $contract->start_date->format('Y-m-d') }}</div>
        <div><span class="label">Status:</span> <span style="color: #b7791f; font-weight: 600;">Awaiting HR Signature</span></div>
    </div>

    <div class="canvas-wrapper">
        <canvas id="signature-pad"></canvas>
    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-clear" id="clear-btn">🗑 Clear</button>
        <button type="button" class="btn btn-submit" id="submit-btn">✅ Sign & Approve</button>
    </div>

    <p class="footer-note">By signing, you confirm approval of this contract.</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.5/signature_pad.umd.min.js">
</script>
<script>
    (function() {
        const canvas = document.getElementById('signature-pad');
        const clearBtn = document.getElementById('clear-btn');
        const submitBtn = document.getElementById('submit-btn');

        const pad = new SignaturePad(canvas, {
            minWidth: 1.5,
            maxWidth: 3.5,
            penColor: "#000000",
            backgroundColor: "rgba(255,255,255,1)"
        });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            pad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 100);

        clearBtn.addEventListener('click', function() {
            pad.clear();
        });

        submitBtn.addEventListener('click', function() {
            if (pad.isEmpty()) {
                alert('Please sign before submitting.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Submitting...';
            clearBtn.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    signature: pad.toDataURL('image/png')
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === false) {
                    alert('❌ Error: ' + (data.message || 'Something went wrong.'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = ' Sign & Approve';
                    clearBtn.disabled = false;
                } else {
                    document.querySelector('.container').innerHTML = `
                        <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo" style="max-width:130px; margin-bottom:20px;">
                        <div style="padding: 20px 0;">
                            <h2 style="color: #4A7C59; margin-bottom: 10px;">Contract Signed!</h2>
                            <p style="color: #4a5568; font-size: 16px; margin: 0;">The contract has been successfully signed and approved.</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('❌ Connection error. Check console for details.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign & Approve';
                clearBtn.disabled = false;
            });
        });
    })();
</script>
</body>
</html>
