<?php

namespace App\Notifications\Payroll;

use App\Models\Salary\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PayrollCreatedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        protected Payroll $payroll
    ) {
    }

    public function via(object $notifiable): array
    {
        return [
            'database',
            'broadcast',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Payroll Available',
            'message' => "Payroll for {$this->payroll->month}/{$this->payroll->year} has been created and is ready for review.",
            'payroll_id' => $this->payroll->id,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'New Payroll Available',
            'message' => "Payroll for {$this->payroll->month}/{$this->payroll->year} has been created and is ready for review.",
            'payroll_id' => $this->payroll->id,
        ];
    }
}