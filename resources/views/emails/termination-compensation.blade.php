@extends('emails.layout_reset_password')

@section('content')

<h2 style="margin-top:0;color:#2c3e50;">
    Termination Compensation Details
</h2>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Hello {{ $terminationRequest->user->full_name }},
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Your immediate employment termination has been officially approved
    by the Masar HR administration.
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    Below are the compensation details based on your unused leave
    balances and the applicable termination compensation.
</p>


{{-- Termination Information --}}

<div style="margin:30px 0;">

    <div style="
        padding:18px 20px;
        background:#f4f6f8;
        border-left:5px solid #4A7C59;
        border-radius:6px;
    ">

        <div style="
            font-size:13px;
            color:#777;
            margin-bottom:6px;
        ">
            Termination Date
        </div>

        <div style="
            font-size:17px;
            color:#2c3e50;
            font-weight:bold;
        ">
            {{ $terminationRequest->termination_date }}
        </div>

    </div>

</div>


{{-- Unused Leave Compensation --}}

<h3 style="
    margin-top:35px;
    color:#2c3e50;
">
    Unused Leave Compensation
</h3>

<p style="font-size:15px;color:#555;line-height:1.7;">
    The following amounts represent compensation for your remaining
    annual and sick leave balances.
</p>


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        border-collapse:collapse;
        margin:20px 0 30px;
    "
>

    <tr>
        <td style="
            padding:12px;
            background:#f4f6f8;
            border-bottom:1px solid #ddd;
            color:#555;
            font-size:14px;
            font-weight:bold;
        ">
            Leave Type
        </td>

        <td align="center" style="
            padding:12px;
            background:#f4f6f8;
            border-bottom:1px solid #ddd;
            color:#555;
            font-size:14px;
            font-weight:bold;
        ">
            Remaining Days
        </td>

        <td align="right" style="
            padding:12px;
            background:#f4f6f8;
            border-bottom:1px solid #ddd;
            color:#555;
            font-size:14px;
            font-weight:bold;
        ">
            Compensation
        </td>
    </tr>


    {{-- Annual Leave --}}

    <tr>
        <td style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            Annual Leave
        </td>

        <td align="center" style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            {{ $leaveCompensation['annual_leave_days'] }}
        </td>

        <td align="right" style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            {{ number_format($leaveCompensation['annual_leave_amount'], 2) }}
        </td>
    </tr>


    {{-- Sick Leave --}}

    <tr>
        <td style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            Sick Leave
        </td>

        <td align="center" style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            {{ $leaveCompensation['sick_leave_days'] }}
        </td>

        <td align="right" style="
            padding:14px 12px;
            border-bottom:1px solid #eee;
            color:#555;
            font-size:14px;
        ">
            {{ number_format($leaveCompensation['sick_leave_amount'], 2) }}
        </td>
    </tr>


    {{-- Total Leave Compensation --}}

    <tr>
        <td colspan="2" style="
            padding:16px 12px;
            color:#2c3e50;
            font-size:15px;
            font-weight:bold;
        ">
            Total Leave Compensation
        </td>

        <td align="right" style="
            padding:16px 12px;
            color:#4A7C59;
            font-size:16px;
            font-weight:bold;
        ">
            {{ number_format($leaveCompensation['total_amount'], 2) }}
        </td>
    </tr>

</table>


{{-- Company Composition Compensation --}}

@if(
    $terminationRequest->immediateTerminationDetail &&
    $terminationRequest->immediateTerminationDetail->subtype === 'company_composition'
)

<h3 style="
    margin-top:35px;
    color:#2c3e50;
">
    Termination Compensation
</h3>

<p style="font-size:15px;color:#555;line-height:1.7;">
    According to the termination details, you are also entitled to
    the following compensation:
</p>

<div style="text-align:center;margin:30px 0;">

    <div style="
        display:inline-block;
        min-width:260px;
        padding:20px 30px;
        background:#f4f6f8;
        border-left:5px solid #4A7C59;
        border-radius:6px;
    ">

        <div style="
            font-size:13px;
            color:#777;
            margin-bottom:8px;
        ">
            Company Composition Compensation
        </div>

        <div style="
            font-size:22px;
            color:#2c3e50;
            font-weight:bold;
        ">
            {{ number_format($immediateCompensation, 2) }}
        </div>

    </div>

</div>

@endif


{{-- Total Compensation --}}

<div style="text-align:center;margin:35px 0;">

    <div style="
        display:inline-block;
        min-width:280px;
        padding:22px 30px;
        background:#f4f6f8;
        border-left:5px solid #4A7C59;
        border-radius:6px;
    ">

        <div style="
            font-size:13px;
            color:#777;
            margin-bottom:8px;
        ">
            Total Compensation
        </div>

        <div style="
            font-size:24px;
            color:#2c3e50;
            font-weight:bold;
        ">
            {{
                number_format(
                    $leaveCompensation['total_amount']
                    + ($immediateCompensation ?? 0),
                    2
                )
            }}
        </div>

    </div>

</div>


<p style="font-size:15px;color:#555;line-height:1.7;">
    The amounts shown above are calculated based on your current
    salary and the leave balances recorded in the Masar HR system
    at the time your termination was processed.
</p>

<p style="font-size:15px;color:#555;line-height:1.7;">
    If you have any questions regarding these compensation amounts,
    please contact the HR department.
</p>

<p style="font-size:14px;color:#777;line-height:1.7;margin-top:35px;">
    This email serves as an official notification of your termination
    compensation details.
</p>

@endsection