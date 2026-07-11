<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Signed — MasarHR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 480px;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .logo { max-width: 130px; margin-bottom: 20px; }
        .icon { font-size: 60px; margin-bottom: 16px; }
        h1 { color: #1a4a3a; margin: 0 0 8px 0; }
        p { color: #4b5563; line-height: 1.6; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 28px;
            background: #4A7C59;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="{{ asset('images/logo.jpg') }}" class="logo" alt="MasarHR">
    <h1>Already Signed</h1>

    @if(isset($type) && $type === 'hr')
        <p><strong>{{ $name }}</strong>, you have already signed this contract as HR.</p>
    @else
        <p><strong>{{ $name }}</strong>, you have already signed this contract.</p>
    @endif

    <p style="font-size: 14px; color: #94a3b8;">No further action is required.</p>
</div>
</body>
</html>
