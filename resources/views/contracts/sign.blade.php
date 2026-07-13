<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contract Signature — MasarHR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            text-align: center;
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 480px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        .canvas-wrapper {
            width: 100%;
            height: 200px;
            margin-bottom: 10px;
            position: relative;
        }
        canvas {
            border: 2px solid #4A7C59;
            border-radius: 8px;
            cursor: crosshair;
            width: 100%;
            height: 100%;
            background: #fff;
            display: block;
            touch-action: none;
        }
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 6px;
            font-weight: bold;
            transition: background 0.2s ease;
        }
        .btn-clear {
            background: #e2e8f0;
            color: #2d3748;
        }
        .btn-clear:hover {
            background: #cbd5e1;
        }
        .btn-submit {
            background: #4A7C59;
            color: #fff;
        }
        .btn-submit:hover {
            background: #3b6347;
        }
        .btn-submit:disabled {
            background: #93c5a3;
            cursor: not-allowed;
        }
        .logo {
            max-width: 130px;
            margin-bottom: 16px;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo">
    <h1 style="color:#1a4a3a; margin-top: 0;">Contract Signature</h1>
    <p style="color: #4a5568; line-height: 1.5;">Welcome <strong>{{ $offer->candidate->full_name }}</strong>, please provide your signature below to acknowledge and accept the contract terms.</p>

    <div class="canvas-wrapper">
        <canvas id="signature-pad"></canvas>
    </div>

    <button type="button" class="btn btn-clear" id="clear-btn"> Clear</button>

    <form id="signature-form">
        @csrf
        <button type="submit" class="btn btn-submit" id="submit-btn">✅ Confirm Signature</button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/4.1.5/signature_pad.umd.min.js"></script>
<script>
    (function() {
        const canvas = document.getElementById('signature-pad');
        const clearBtn = document.getElementById('clear-btn');
        const submitBtn = document.getElementById('submit-btn');

        // ✅ Check if already signed
        const alreadySigned = {{ isset($alreadySigned) && $alreadySigned ? 'true' : 'false' }};

        if (alreadySigned) {
            document.querySelector('.canvas-wrapper').innerHTML = '<p style="color: #4A7C59; font-size: 18px; padding: 30px;">✅ You have already signed this contract.</p>';
            submitBtn.disabled = true;
            submitBtn.textContent = '✅ Already Signed';
            clearBtn.disabled = true;
        }

        // ✅ 1. ضبط حجم الـ Canvas
        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);
        }

        resizeCanvas();

        // ✅ 2. تعريف الـ SignaturePad
        const pad = new SignaturePad(canvas, {
            throttle: 16,
            minWidth: 1.5,
            maxWidth: 3.5,
            penColor: "#0f172a",
            backgroundColor: "rgba(255,255,255,1)"
        });

        window.addEventListener("resize", function() {
            resizeCanvas();
            pad.clear();
        });

        // ✅ 3. زر المسح
        clearBtn.addEventListener('click', function() {
            pad.clear();
        });

        // ✅ 4. إرسال التوقيع
        document.getElementById('signature-form').addEventListener('submit', function(e) {
            e.preventDefault();

            if (pad.isEmpty()) {
                alert('Please provide your signature before submitting.');
                return;
            }

            // ✅ تعطيل الزرين أثناء الإرسال
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Submitting...';
            clearBtn.disabled = true;

            const token = document.querySelector('input[name="_token"]').value;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ signature: pad.toDataURL('image/png') })
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                }
               if (data.success) {
    document.querySelector('.container').innerHTML = `
        <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR Logo" style="max-width:130px; margin-bottom:20px;">
        <div style="padding: 20px 0;">
            <h2 style="color: #4A7C59; margin-bottom: 10px;">Signature Submitted!</h2>
            <p style="color: #4a5568; font-size: 16px; margin: 0;">Thank you, your signature has been received successfully.</p>
            <p style="color: #94a3b8; font-size: 14px; margin-top: 10px;">You will be contacted shortly.</p>
        </div>
    `;
} else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '✅ Confirm Signature';
                    clearBtn.disabled = false;
                }
            })
            .catch(() => {
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = '✅ Confirm Signature';
                clearBtn.disabled = false;
            });
        });
    })();
</script>
</body>
</html>
