@extends('emails.layout_reset_password')

@section('content')

<h2 style="margin-top:0;color:#2c3e50;">
    Employment Termination Notice
</h2>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Hello {{ $employee->full_name }},
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    We are writing to inform you that your employment termination
    has been officially approved by the Masar HR administration.
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Your employment will remain active until the following date:
</p>


{{-- Last Working Day --}}

<div style="text-align:center;margin:35px 0;">

    <div
        style="
            display:inline-block;
            min-width:260px;
            padding:20px 30px;
            background:#f4f6f8;
            border-left:5px solid #4A7C59;
            border-radius:6px;
        "
    >

        <div
            style="
                font-size:13px;
                color:#777;
                margin-bottom:8px;
            "
        >
            Last Working Day
        </div>

        <div
            style="
                font-size:22px;
                color:#2c3e50;
                font-weight:bold;
            "
        >
            {{ $terminationRequest->last_working_day }}
        </div>

    </div>

</div>


<p style="font-size:15px;color:#555;line-height:1.7;">
    Please make sure to complete any required handover procedures
    and return company property before your last working day.
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    If you have any questions regarding your termination or the
    next steps, please contact the HR department.
</p>

<p style="font-size:14px;color:#777;line-height:1.7;margin-top:35px;">
    This email serves as an official notification of the final
    termination decision.
</p>

@endsection