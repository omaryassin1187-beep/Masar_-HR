<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">

    <!-- Logo -->
    <div style="text-align: center; margin-bottom: 15px;">
        <div class="logo">
            <img src="{{ asset('images/logo.jpg') }}" alt="MasarHR Logo">
        </div>
    </div>

    <!-- Header -->
    <div style="background-color: #4A7C59; padding: 20px; border-radius: 8px 8px 0 0;">
        <h1 style="color: #ffffff; margin: 0;">Final Settlement</h1>
        <p style="color: #e0f0e0; margin: 5px 0 0 0;">MasarHR Resignation & Offboarding</p>
    </div>

    <!-- Body -->
    <div style="background-color: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
        <p style="color: #334155;">Dear <strong>{{ $employee->full_name }}</strong>,</p>

        <p style="color: #334155;">
            Your {{ $resignation->isImmediate() ? 'immediate resignation' : 'resignation' }} process has been completed as of
            <strong>{{ $resignation->last_working_day->format('F d, Y') }}</strong>.
            Below are the details of your final settlement:
        </p>

        <!-- Settlement Table -->
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold;">Item</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #f1f5f9; font-weight: bold; text-align: right;">Amount</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0;">Annual Leave Compensation ({{ $settlement->annual_leave_days }} days)</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: right;">{{ number_format($settlement->annual_leave_amount, 2) }}</td>
            </tr>

            @if($settlement->sick_leave_days > 0)
                <tr>
                    <td style="padding: 10px; border: 1px solid #e2e8f0;">Sick Leave Compensation ({{ $settlement->sick_leave_days }} days)</td>
                    <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: right;">{{ number_format($settlement->sick_leave_amount, 2) }}</td>
                </tr>
            @endif

            @if(!is_null($settlement->end_of_service_gratuity) && $settlement->end_of_service_gratuity > 0)
                <tr>
                    <td style="padding: 10px; border: 1px solid #e2e8f0;">End of Service Gratuity</td>
                    <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: right;">{{ number_format($settlement->end_of_service_gratuity, 2) }}</td>
                </tr>
            @endif

            <!-- ✅ Notice Period Amount - مع التصنيف حسب نوع الإخلال -->
            @if(!is_null($settlement->notice_period_amount))
                <tr>
                    <td style="padding: 10px; border: 1px solid #e2e8f0;">
                        @if($resignation->hr_classification === 'breach_by_company')
                            Compensation for Company's Breach (Notice Period)
                        @elseif($resignation->hr_classification === 'breach_by_employee')
                            Notice Period Deduction (Employee's Breach)
                        @else
                            {{ $resignation->notice_period_treatment === 'compensate' ? 'Notice Period Compensation' : 'Notice Period Deduction' }}
                        @endif
                    </td>
                    <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: right;">
                        {{ $resignation->notice_period_treatment === 'deduct' ? '-' : '' }}{{ number_format($settlement->notice_period_amount, 2) }}
                    </td>
                </tr>
            @endif

            <tr>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #e8f0fe; font-weight: bold;">Total Settlement</td>
                <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: #e8f0fe; font-weight: bold; text-align: right;">
                    {{ number_format($settlement->total_compensation_amount, 2) }}
                </td>
            </tr>
        </table>

        <!-- Salary Note -->
        <div style="background-color: #e8f0fe; padding: 12px 16px; border-radius: 6px; margin-top: 16px;">
            <p style="color: #1e3a5f; margin: 0; font-size: 13px;">
                <strong>📌 Salary Note:</strong><br>
                This settlement is separate from your salary. Your prorated salary for the actual working days will be processed in the next payroll cycle and you will receive a separate notification with the payslip.
            </p>
        </div>

        <!-- HR Notes -->
        @if($resignation->isImmediate() && $resignation->hr_classification_notes)
            <div style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 6px; margin-top: 12px;">
                <p style="color: #475569; margin: 0; font-size: 13px;">
                    <strong>📝 HR Note:</strong><br>
                    {{ $resignation->hr_classification_notes }}
                </p>
            </div>
        @endif

        <p style="color: #334155; margin-top: 24px;">
            If you have any questions regarding this settlement, please contact the HR department.
        </p>

        <p style="color: #334155;">
            Best regards,<br>
            <strong>MasarHR Team</strong>
        </p>
    </div>

    <!-- Footer -->
    <p style="text-align: center; color: #94a3b8; font-size: 12px; margin-top: 20px;">
        © {{ date('Y') }} MasarHR. All rights reserved.
    </p>

</body>
</html>
