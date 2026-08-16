<?php

namespace App\Mail;

use App\Models\Salary\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payslip $payslip
    ) {
        $this->payslip->loadMissing(['user.department', 'payroll']);
    }

    public function build()
    {
        $period = \Carbon\Carbon::create(
            $this->payslip->payroll->year,
            $this->payslip->payroll->month
        )->format('F Y');

        return $this
            ->subject("Payroll Statement - {$period}")
            ->view('payslips.mail', [
                'payslip'     => $this->payslip,
                'generatedAt' => now(),
                'logoPath'    => public_path('images/logo.jpg'),
            ]);
    }
}