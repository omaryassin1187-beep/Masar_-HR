<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Set Your Password — MasarHR</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 40px 35px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        .logo { text-align: center; margin-bottom: 25px; }
        .logo img { max-width: 130px; height: auto; }
        h2 { color: #4A7C59; text-align: center; margin: 0 0 20px 0; font-size: 22px; }
        input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #4A7C59;
            box-shadow: 0 0 0 2px rgba(74,124,89,0.15);
        }
        .btn {
            background-color: #4A7C59;
            color: #fff;
            border: none;
            padding: 14px;
            width: 100%;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
            font-weight: bold;
        }
        .btn:hover { background-color: #3d6b4a; }
        .btn-link {
            text-decoration: none;
            display: block;
            text-align: center;
        }
        .message { text-align: center; margin: 15px 0; font-size: 14px; }
        .success { color: #4A7C59; }
        .error { color: #dc2626; }
        .login-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <img src="{{ asset('images/logo.jpg') }}" alt="MasarHR Logo">
    </div>
    <h2>Set Your Password</h2>

    <div id="message" class="message"></div>

    <form id="passwordForm">
        <input type="email" id="email" placeholder="Email" readonly>
        <input type="password" id="password" placeholder="New Password" required minlength="8">
        <input type="password" id="password_confirmation" placeholder="Confirm Password" required minlength="8">
        <button type="submit" class="btn">Save Password</button>
    </form>

    <div id="loginLink" class="login-link" style="display:none;">
        <a href="http://localhost:3000/login" class="btn btn-link">Go to Login</a>
    </div>
</div>

<script>
    const params = new URLSearchParams(window.location.search);
    document.getElementById('email').value = params.get('email') || '';

    document.getElementById('passwordForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;

        if (password !== confirmation) {
            document.getElementById('message').innerHTML = '<p class="error">Passwords do not match.</p>';
            return;
        }

        const response = await fetch('/api/putPassword', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: password,
                password_confirmation: confirmation,
            })
        });

        const result = await response.json();
        const msg = document.getElementById('message');

        if (response.ok) {
            msg.innerHTML = '<p class="success">Password set successfully!</p>';
            document.getElementById('passwordForm').style.display = 'none';
            document.getElementById('loginLink').style.display = 'block';
        } else {
            msg.innerHTML = '<p class="error">' + (result.message || 'Error setting password.') + '</p>';
        }
    });
</script>

</body>
</html>
