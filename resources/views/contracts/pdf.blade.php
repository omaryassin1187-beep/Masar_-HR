<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employment Contract - {{ $user->full_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            color: #2d3748;
            line-height: 1.6;
            margin: 10px;
        }

        /* الهوية البصرية لشركة MasarHR */
        .brand-header {
            border-bottom: 3px solid #1a4a3a; /* الأخضر الداكن المستوحى من الشعار */
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .contract-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #1a4a3a;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contract-subtitle {
            text-align: center;
            font-size: 11px;
            color: #718096;
            margin-bottom: 20px;
        }

        /* الديباجة القانونية */
        .preamble {
            background: #f7fafc;
            border-left: 4px solid #b7791f; /* لمسة ذهبية/بنية متناسقة مع الشعار */
            padding: 12px 15px;
            margin-bottom: 25px;
            font-size: 11.5px;
            text-align: justify;
        }

        /* تنسيق الجدول الاحترافي */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a4a3a;
            margin-top: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background: #f8fafc;
            color: #4a5568;
            font-weight: 600;
            width: 35%;
        }

        td {
            color: #1a202c;
        }

        .highlight-salary {
            font-weight: bold;
            color: #2f855a; /* لون أخضر مالي للمرتب */
            background-color: #f0fff4;
        }

        /* الشروط القانونية الإضافية حسب قانون العمل السوري */
        .legal-clauses {
            font-size: 11px;
            color: #4a5568;
            margin-bottom: 30px;
        }

        .legal-clauses ol {
            padding-left: 20px;
            margin-top: 5px;
        }

        .legal-clauses li {
            margin-bottom: 5px;
            text-align: justify;
        }

        /* قسم التواقيع */
        .signatures-container {
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid; /* لمنع انقسام التواقيع في صفحة جديدة بمفردها */
        }

        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }

        .signature-box.right {
            float: right;
        }

        .signature-line {
            border-top: 1px solid #a0aec0;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
            font-weight: bold;
            color: #2d3748;
        }

        .clear {
            clear: both;
        }

        .footer {
            margin-top: 60px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="brand-header">
    <div class="logo-container">
        <img src="{{ public_path('images/logo.jpg') }}" alt="MasarHR Logo" style="max-width: 130px; height: auto;">
    </div>
    <div class="contract-title">Employment Contract</div>
    <div class="contract-subtitle">Contract Reference Number: <strong>#{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
</div>

<!-- الديباجة القانونية المضافة وتحديد الأطراف -->
<div class="preamble">
    <strong>PREAMBLE:</strong> This Employment Contract is entered into pursuant to the <strong>Syrian Labor Law No. 17 of 2010</strong>, by and between: <br>
    <strong>First Party (Employer):</strong> MasarHR, legally represented by its Authorized Manager.<br>
    <strong>Second Party (Employee):</strong> {{ $user->full_name }} (Email: {{ $user->email }}).<br>
    Both parties having full legal capacity have agreed to the terms and conditions set forth below.
</div>

<div class="section-title">1. Core Employment Terms</div>
<table>
    <tr>
        <th>Employee Name</th>
        <td><strong>{{ $user->full_name }}</strong></td>
    </tr>
    <tr>
        <th>Job Title </th>
    <td><strong>{{ $jobTitle }}</strong></td>
    </tr>
    <tr>
        <th>Start Date</th>
        <td>{{ $contract->start_date->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <th>End Date</th>
        <td>{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : 'Indefinite / غير محدد المدة' }}</td>
    </tr>
    <tr>
        <th>Probation Period</th>
        <td>{{ $contract->probation_period_days }} days (Until {{ $contract->probationEndsAt()->format('Y-m-d') }})</td>
    </tr>
</table>

<div class="section-title">2. Compensation & Working Hours</div>
<table>
    <tr>
        <th>Hourly Rate</th>
        <td>${{ number_format($contract->hour_price, 2) }}</td>
    </tr>
    <tr>
        <th>Working Hours / Day</th>
        <td>{{ $contract->working_hours_per_day }} hours</td>
    </tr>
    <tr>
        <th>Weekend Days</th>
        <td style="text-transform: capitalize;">{{ implode(', ', (array) $contract->weekend_days) }}</td>
    </tr>
    <tr>
        <th>Est. Monthly Salary</th>
        <td class="highlight-salary">${{ number_format($contract->estimatedMonthlySalary(), 2) }}</td>
    </tr>
</table>

<div class="section-title">3. Legal & Termination Framework</div>
<table>
    <tr>
        <th>Termination Notice</th>
        <td>{{ $contract->termination_notice_days }} days</td>
    </tr>
    <tr>
        <th>Governing Law & Jurisdiction</th>
        <td>{{ $contract->jurisdiction ?? 'Syrian Labor Law No.17 of 2010 / Damascus Courts' }}</td>
    </tr>
    <tr>
        <th>Status</th>
        <td>
            @if($contract->signed_at)
                Signed on {{ $contract->signed_at->format('Y-m-d') }}
            @else
                <span style="color: #c53030; font-weight: bold;">Pending Signature</span>
            @endif
        </td>
    </tr>
</table>

<!-- بنود تضمن سلامة العقد طبقاً للقانون السوري -->
<div class="legal-clauses">
    <div style="font-weight: bold; color: #1a4a3a; margin-bottom: 5px;">GENERAL PROVISIONS:</div>
    <ol>
        <li>The Second Party complies with the internal regulations and policies of the First Party, provided they do not conflict with Syrian Labor Law No. 17 of 2010.</li>
        <li>This contract has been executed in three (3) original copies; one copy has been delivered to each party, and the third shall be deposited with the competent Ministry of Social Affairs and Labor.</li>
        <li>In the event of any discrepancy between the English text and the Arabic text (if registered officially), the Arabic text shall prevail before the Syrian Judicial Authorities.</li>
    </ol>
</div>

<!-- قسم التواقيع الرسمي -->
<div class="signatures-container">
    <div class="signature-box">
        <p>For First Party (Employer)<br><strong style="color: #1a4a3a;">MasarHR</strong></p>
        <div class="signature-line">Authorized Seal & Signature</div>
    </div>

    <div class="signature-box right">
        <p>For Second Party (Employee)<br><strong>{{ $user->full_name }}</strong></p>
        <div class="signature-line">Employee Signature & Date</div>
    </div>
    <div class="clear"></div>
</div>

<div class="footer">
    This is an official document generated securely by MasarHR Platform on {{ now()->format('Y-m-d H:i') }}.<br>
    &copy; {{ now()->format('Y') }} MasarHR. All rights reserved.
</div>

</body>
</html>
