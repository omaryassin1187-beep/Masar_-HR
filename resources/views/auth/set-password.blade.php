<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Set Your Password — MasarHR</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 16px;
        }
        .container {
            background: #ffffff;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            width: 100%;
            max-width: 420px;
            border: 1px solid #e2e8f0;
        }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { max-width: 120px; height: auto; }
        h2 { color: #1a4a3a; text-align: center; font-size: 22px; margin: 0 0 6px 0; }
        .subtitle { color: #64748b; text-align: center; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; color: #2d3748; font-size: 14px; margin-bottom: 4px; }
        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            background: #f9fafb;
            transition: 0.2s;
        }
        input:focus { outline: none; border-color: #4A7C59; box-shadow: 0 0 0 3px rgba(74,124,89,0.15); }
        input[readonly] { background: #f1f5f9; color: #334155; }
        .btn {
            background-color: #4A7C59; color: #ffffff; border: none; padding: 16px;
            width: 100%; font-size: 17px; border-radius: 8px; font-weight: bold;
            cursor: pointer; margin-top: 6px; text-decoration: none; display: block; text-align: center;
        }
        .btn:hover { background-color: #3d6b4a; }
        .message { text-align: center; margin: 12px 0; font-size: 14px; }
        .success { color: #4A7C59; }
        .error { color: #dc2626; }
        .warning { color: #f59e0b; }
        .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 24px; }
        .login-link { margin-top: 18px; }
        .hidden { display: none; }
        .success-box { text-align: center; padding: 20px 0; }
        .success-box .icon { font-size: 50px; margin-bottom: 10px; }
        .success-box h3 { color: #4A7C59; margin-bottom: 8px; }
        .success-box p { color: #64748b; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">

    <div class="logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="MasarHR Logo">
    </div>

    {{-- ✅ إخفاء العنوان إذا كان alreadySet = true --}}
    @if(!$alreadySet)
        <h2>Set Your Password</h2>
        <p class="subtitle">Create your account password to get started</p>
    @endif

    <div id="message" class="message"></div>

    {{-- ✅ صندوق النجاح (إذا كان alreadySet = true) --}}
    <div id="successBox" class="{{ $alreadySet ? '' : 'hidden' }} success-box">
        <h3>Password Already Configured!</h3>
        <p>Your account password has been set previously. You cannot change it through this token link again.</p>
    </div>

    {{-- ✅ الفورم (يظهر فقط إذا alreadySet = false) --}}
    <form id="passwordForm" class="{{ $alreadySet ? 'hidden' : '' }}">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" value="{{ $email }}" readonly>
        </div>

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" placeholder="Enter new password" required minlength="8">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" placeholder="Confirm your password" required minlength="8">
        </div>

        <button type="submit" class="btn" id="submitBtn">Save Password</button>
    </form>

    <div id="loginLink" class="login-link {{ $alreadySet ? '' : 'hidden' }}">
        <a href="http://localhost:3000/login" class="btn">Go to Login</a>
    </div>

</div>



<script>
    let isSaved = {{ $alreadySet ? 'true' : 'false' }};

    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (isSaved) {
            document.getElementById('message').innerHTML = '<p class="warning">⚠️ Password already set. Action denied.</p>';
            return;
        }

        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;

        if (password !== confirmation) {
            document.getElementById('message').innerHTML = '<p class="error">Passwords do not match.</p>';
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        try {
            const response = await fetch('/api/putPassword', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: password,
                    password_confirmation: confirmation,
                })
            });

            const result = await response.json();
            const msg = document.getElementById('message');

            if (response.ok) {
                isSaved = true;
                msg.innerHTML = '<p class="success"> Password set successfully!</p>';
                document.getElementById('passwordForm').style.display = 'none';
                document.getElementById('loginLink').classList.remove('hidden');
                document.getElementById('successBox').classList.remove('hidden');
                document.querySelector('#successBox h3').innerText = "Password Set Successfully!";
                document.querySelector('#successBox p').innerText = "Your account is now ready. Click below to login.";
            } else {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Password';
                msg.innerHTML = '<p class="error">❌ ' + (result.message || 'Error setting password.') + '</p>';
            }
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Password';
            document.getElementById('message').innerHTML = '<p class="error">❌ An unexpected error occurred.</p>';
        }
    });
</script>

</body>
</html>
