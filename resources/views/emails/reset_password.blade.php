@extends('emails.layout_reset_password')

@section('content')

<h2 style="margin-top:0;color:#2c3e50;">
    Reset Your Password
</h2>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Hello {{ $user->full_name }},
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    We received a request to reset your account password.
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Click the button below to choose a new password.
</p>

<div style="text-align:center;margin:40px 0;">

    <a
        href="{{ route('password.reset.form',[
            'token'=>$token,
            'email'=>$user->email
        ]) }}"

        style="
            display:inline-block;
            padding:14px 40px;
            background:#4A7C59;
            color:#ffffff;
            text-decoration:none;
            border-radius:6px;
            font-size:16px;
            font-weight:bold;
        ">

        Reset Password

    </a>

</div>

<p style="font-size:14px;color:#777;line-height:1.7;">
    This password reset link will expire in <strong>60 minutes</strong>.
</p>

<p style="font-size:14px;color:#777;line-height:1.7;">
    If you didn't request a password reset, you can safely ignore this email.
</p>

@endsection