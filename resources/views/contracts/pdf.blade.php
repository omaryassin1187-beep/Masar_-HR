<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employment Contract</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            color: #2d3748;
            line-height: 1.6;
            margin: 10px;
        }
        p, td, th, li, div {
            font-family: 'DejaVu Sans', 'Arial', 'Helvetica', sans-serif;
        }
        .brand-header {
            border-bottom: 3px solid #1a4a3a;
            padding-bottom: 15px;
            margin-bottom: 25px;
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
        .preamble {
            background: #f7fafc;
            border-left: 4px solid #b7791f;
            padding: 12px 15px;
            margin-bottom: 25px;
            font-size: 11.5px;
            text-align: justify;
        }
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
            color: #2f855a;
            background-color: #f0fff4;
        }
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
        .signatures-container {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-image {
            max-width: 180px;
            max-height: 80px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px;
            margin-top: 10px;
        }
        .signature-date {
            font-size: 10px;
            color: #718096;
            margin-top: 5px;
        }
        .signature-pending {
            color: #a0aec0;
            font-size: 12px;
            margin-top: 10px;
            font-style: italic;
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

@php
    use Carbon\Carbon;

    // 1. معالجة وتجهيز التواريخ
    $startDate       = Carbon::parse($contract->start_date);
    $endDate         = $contract->end_date ? Carbon::parse($contract->end_date) : null;
    $signedAt        = $contract->signed_at ? Carbon::parse($contract->signed_at) : null;
    $candidateSigned = $contract->candidate_signed_at ? Carbon::parse($contract->candidate_signed_at) : null;
    $hrSigned        = $contract->hr_signed_at ? Carbon::parse($contract->hr_signed_at) : null;

    $probationEnd = method_exists($contract, 'probationEndsAt')
        ? $contract->probationEndsAt()
        : $startDate->copy()->addDays($contract->probation_period_days);
    $probationEnd = Carbon::parse($probationEnd);

    // 2. قراءة التواقيع الثنائية وتحويلها لـ Base64 بأمان (كتلة موحدة ومحمية)
    $candidateSigBase64 = null;
    $hrSigBase64 = null;

    if ($contract->candidate_signature_path) {
        $candidatePath = storage_path('app/private/' . $contract->candidate_signature_path);
        if (file_exists($candidatePath)) {
            $candidateSigBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($candidatePath));
        }
    }

    if ($contract->hr_signature_path) {
        $hrPath = storage_path('app/private/' . $contract->hr_signature_path);
        if (file_exists($hrPath)) {
            $hrSigBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($hrPath));
        }
    }
@endphp

<div class="brand-header">
    <div class="contract-title">Employment Contract</div>
    <div class="contract-subtitle">Contract Reference Number: <strong>#{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
</div>

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
        <th>Job Title</th>
        <td><strong>{{ $jobTitle }}</strong></td>
    </tr>
    <tr>
        <th>Start Date</th>
        <td>{{ $startDate->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <th>End Date</th>
        <td>{{ $endDate ? $endDate->format('Y-m-d') : 'Indefinite' }}</td>
    </tr>
    <tr>
        <th>Probation Period</th>
        <td>{{ $contract->probation_period_days }} days (Until {{ $probationEnd->format('Y-m-d') }})</td>
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
        <td>{{ $contract->working_hour_per_day }} hours</td>
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
            @if($signedAt)
                Signed on {{ $signedAt->format('Y-m-d') }}
            @elseif($candidateSigned)
                Awaiting HR Signature
            @else
                <span style="color: #c53030; font-weight: bold;">Pending Signature</span>
            @endif
        </td>
    </tr>
</table>

<div class="legal-clauses">
    <div style="font-weight: bold; color: #1a4a3a; margin-bottom: 5px;">GENERAL PROVISIONS:</div>
    <ol>
        <li>The Second Party complies with the internal regulations and policies of the First Party, provided they do not conflict with Syrian Labor Law No. 17 of 2010.</li>
        <li>This contract has been executed in three (3) original copies; one copy has been delivered to each party, and the third shall be deposited with the competent Ministry of Social Affairs and Labor.</li>
        <li>In the event of any discrepancy between the English text and the Arabic text (if registered officially), the Arabic text shall prevail before the Syrian Judicial Authorities.</li>
    </ol>
</div>

<div class="signatures-container">
    <table style="border: none; margin-top: 30px;">
        <tr>
            <td style="border: none; width: 50%; text-align: center; vertical-align: top;">
                <p style="font-weight: bold; color: #2d3748; margin-bottom: 5px;">Employee Signature</p>
                <p style="font-size: 11px; color: #4a5568; margin-top: 0;">{{ $user->full_name }}</p>
                @if($candidateSigBase64)
                    <img src="{{ $candidateSigBase64 }}" class="signature-image"><br>
                    <p class="signature-date">Signed: {{ $candidateSigned ? $candidateSigned->format('Y-m-d H:i') : '' }}</p>
                @else
                    <p class="signature-pending">Awaiting Signature</p>
                @endif
            </td>

            <td style="border: none; width: 50%; text-align: center; vertical-align: top;">
                <p style="font-weight: bold; color: #2d3748; margin-bottom: 5px;">Employer Signature (HR)</p>
                <p style="font-size: 11px; color: #4a5568; margin-top: 0;">MasarHR</p>
                @if($hrSigBase64)
                    <img src="{{ $hrSigBase64 }}" class="signature-image"><br>
                    <p class="signature-date">Signed: {{ $hrSigned ? $hrSigned->format('Y-m-d H:i') : '' }}</p>
                @else
                    <p class="signature-pending">Awaiting Signature</p>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    This is an official document generated securely by MasarHR Platform on {{ now()->format('Y-m-d H:i') }}.<br>
    &copy; {{ now()->format('Y') }} MasarHR. All rights reserved.
</div>

</body>
</html>
