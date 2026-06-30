<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract Preview - {{ $user->full_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            color: #2d3748;
            line-height: 1.6;
            margin: 10px;
        }
        .brand-header {
            border-bottom: 3px solid #1a4a3a;
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
            margin-top: 50px;
            width: 100%;
            page-break-inside: avoid;
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
        .signature-pending {
            color: #a0aec0;
            font-size: 12px;
            margin-top: 10px;
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
    <div class="contract-subtitle">Offer Reference: <strong>#{{ str_pad($offer->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
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
        <td>{{ \Carbon\Carbon::parse($offer->start_date)->format('Y-m-d') }}</td>
    </tr>
   <tr>
    <th>End Date</th>
    <td>{{ \Carbon\Carbon::parse($offer->start_date)->addYear()->format('Y-m-d') }}</td>
</tr>
    <tr>
        <th>Probation Period</th>
    <td>{{ $probationDays }} days</td>
    </tr>
</table>

<div class="section-title">2. Compensation & Working Hours</div>
<table>
    <tr>
        <th>Hourly Rate</th>
        <td>${{ number_format($offer->hour_price, 2) }}</td>
    </tr>
    <tr>
        <th>Working Hours / Day</th>
        <td>{{ $offer->working_hours_per_day }} hours</td>
    </tr>
    <tr>
        <th>Weekend Days</th>
        <td style="text-transform: capitalize;">{{ implode(', ', (array) $offer->weekend_days) }}</td>
    </tr>
</table>

<div class="section-title">3. Legal & Termination Framework</div>
<table>
    <tr>
        <th>Governing Law & Jurisdiction</th>
        <td>Syrian Labor Law No.17 of 2010 / Damascus Courts</td>
    </tr>
    <tr>
        <th>Status</th>
        <td><span style="color: #b7791f; font-weight: bold;">Awaiting Signature</span></td>
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

<!-- Signatures Section -->
<table style="width: 100%; border: none; margin-top: 50px; page-break-inside: avoid; box-shadow: none;">
    <tr>
        <td style="width: 45%; border: none; text-align: center; padding: 0;">
            <div style="border-top: 1px solid #a0aec0; padding-top: 8px;">
                <p style="font-weight: bold; color: #2d3748; margin: 0 0 5px 0;">Employee Signature</p>
                <p style="font-size: 11px; color: #4a5568; margin: 0 0 5px 0;">{{ $user->full_name }}</p>
                <p style="color: #a0aec0; font-size: 12px; margin: 0;" class="signature-pending">Awaiting Signature</p>
            </div>
        </td>


        <td style="width: 10%; border: none;"></td>
        <td style="width: 45%; border: none; text-align: center; padding: 0;">
            <div style="border-top: 1px solid #a0aec0; padding-top: 8px;">
                <p style="font-weight: bold; color: #2d3748; margin: 0 0 5px 0;">Employer Signature (HR)</p>
                <p style="font-size: 11px; color: #4a5568; margin: 0 0 5px 0;">MasarHR</p>
                <p style="color: #a0aec0; font-size: 12px; margin: 0;" class="signature-pending">Awaiting Signature</p>
            </div>
        </td>
    </tr>
</table>

<div class="footer">
    This is a preview generated by MasarHR Platform on {{ now()->format('Y-m-d H:i') }}.<br>
    &copy; {{ now()->format('Y') }} MasarHR. All rights reserved.
</div>

</body>
</html>
