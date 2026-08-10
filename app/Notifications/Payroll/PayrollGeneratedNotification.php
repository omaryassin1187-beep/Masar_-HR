<?php

namespace App\Notifications\Payroll;

use App\Models\Salary\Payslip;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PayrollGeneratedNotification extends Notification implements ShouldBroadcastNow
{
    public function __construct(
        protected Payslip $payslip
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payroll Generated',
            'message' => 'Your payroll has been generated successfully.',
            'payroll_id' => $this->payslip->payroll_id,
            'payslip_id' => $this->payslip->id,
            'type' => 'payroll_generated',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            $this->toArray($notifiable)
        );
    }
}