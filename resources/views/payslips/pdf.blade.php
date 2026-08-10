<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Payroll Statement</title>
</head>

<body style="font-family: 'DejaVu Sans', sans-serif; color: #334155; margin: 35px; font-size: 12px; line-height: 1.6;">

    {{-- ================= HEADER WITH LOGO & GREEN BORDER ================= --}}
    <div style="text-align: center; border-bottom: 3px solid #4A7C59; padding-bottom: 18px; margin-bottom: 30px;">
        
        <!-- صورة اللوغو في الوسط -->
        <div style="margin-bottom: 10px;">
            <img src="{{ public_path('images/logo.jpg') }}" alt="Masar HR Logo" style="max-height: 180px; width: auto;">
        </div>

        <div style="font-size: 21px; color: #4A7C59; font-weight: bold; letter-spacing: 1px;">
            PAYROLL STATEMENT
        </div>

        <div style="margin-top: 8px; color: #64748b; font-size: 12px;">
            Payroll Period : 
            {{ \Carbon\Carbon::create($payslip->payroll->year, $payslip->payroll->month)->format('F Y') }}
        </div>

    </div>

    <p style="color: #334155; margin-bottom: 20px;">
        Dear <strong>{{ $payslip->user->full_name }}</strong>  ,
    </p>

    <p>your salary for this month :</p>

    {{-- ================= EMPLOYEE INFORMATION ================= --}}
    <div style="background: #4A7C59; color: white; padding: 8px 12px; font-size: 13px; font-weight: bold; margin-top: 25px;">
        Employee Information
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 45%;">Employee</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->user->full_name }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Department</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->user->department->name }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Payroll Month</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->payroll->month }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Payroll Year</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->payroll->year }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Generated At</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $generatedAt->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

    {{-- ================= SALARY DETAILS ================= --}}
    <div style="background: #4A7C59; color: white; padding: 8px 12px; font-size: 13px; font-weight: bold; margin-top: 25px;">
        Salary Details
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 45%;">Hourly Rate</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; ">${{ number_format($payslip->hourly_rate, 2) }} / hour</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Working Hours / Day</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->working_hours_per_day }} hours</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Working Days</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->working_days }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; color: #4A7C59;">Base Salary</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;  font-weight: bold; color: #4A7C59;">
                ${{ number_format($payslip->base_salary, 2) }}
            </td>
        </tr>
    </table>

    {{-- ================= EARNINGS ================= --}}
    <div style="background: #4A7C59; color: white; padding: 8px 12px; font-size: 13px; font-weight: bold; margin-top: 25px;">
        Earnings
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 45%;">Base Salary</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; ">
                ${{ number_format($payslip->base_salary, 2) }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Overtime</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; ">
                ${{ number_format($payslip->overtime_amount, 2) }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Incentives</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0; ">
                ${{ number_format($payslip->incentive_amount, 2) }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; color: #4A7C59;">Gross Salary</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;  font-weight: bold; color: #4A7C59;">
                ${{ number_format($payslip->gross_salary, 2) }}
            </td>
        </tr>
    </table>

    {{-- ================= DEDUCTIONS ================= --}}
    <div style="background: #4A7C59; color: white; padding: 8px 12px; font-size: 13px; font-weight: bold; margin-top: 25px;">
        Deductions
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; width: 45%;">Attendance Deductions</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;  color: #dc2626;">
                -${{ number_format($payslip->deductions_amount, 2) }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Unpaid Leave Days</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;">{{ $payslip->unpaid_leave_days }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Unpaid Leave Amount</td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;  color: #dc2626;">
                -${{ number_format($payslip->unpaid_leave_amount, 2) }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; color: #dc2626;">
                Total Deductions
            </td>
            <td style="padding: 10px; border: 1px solid #e2e8f0;  font-weight: bold; color: #dc2626;">
                -${{ number_format($payslip->deductions_amount + $payslip->unpaid_leave_amount, 2) }}
            </td>
        </tr>
    </table>

    {{-- ================= NET SALARY BOX ================= --}}
    <div style="margin-top: 30px; border: 2px solid #4A7C59; background: #f1f5f9; text-align: center; padding: 20px; border-radius: 6px;">
        <div style="color: #64748b; font-size: 14px; font-weight: bold; text-transform: uppercase;">
            Final Net Salary
        </div>
        <div style="color: #4A7C59; font-size: 30px; font-weight: bold; margin-top: 8px;">
            ${{ number_format($payslip->net_salary, 2) }}
        </div>
    </div>

    {{-- ================= NOTES ================= --}}
    <div style="background: #4A7C59; color: white; padding: 8px 12px; font-size: 13px; font-weight: bold; margin-top: 25px;">
        Notes
    </div>
    <div style="border: 1px solid #e2e8f0; padding: 12px; min-height: 50px; color: #64748b; background-color: #ffffff;">
        {{ $payslip->notes ?: 'No additional notes.' }}
    </div>

    {{-- ================= FOOTER ================= --}}
    <div style="margin-top: 45px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; color: #64748b; font-size: 11px;">
        <p style="margin: 0 0 10px 0; color: #334155;">
            Best regards,<br>
            <strong>MasarHR Team</strong>
        </p>
        Generated on {{ $generatedAt->format('Y-m-d H:i') }}<br>
        © {{ now()->year }} MasarHR. All rights reserved.
    </div>

</body>

</html>