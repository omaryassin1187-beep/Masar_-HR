<?php
// app/Notifications/HrActionRequiredNotification.php
namespace App\Notifications\contracts;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HrActionRequiredNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public Contract $contract,
        public string $signUrl,
        public string $resignUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database' , 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $offer     = $this->contract->offer;
        $candidate = $offer->candidate;

        $pdfBinary = Pdf::loadView('contracts.pdf', [
            'contract' => $this->contract,
            'user'     => $candidate,
            'jobTitle' => $offer->jobPosting->requisition->job_title,
        ])->setPaper('a4', 'portrait')->output();

        return (new MailMessage)
            ->subject('📄 MasarHR _Action Required: Sign Candidate Contract')
            ->view('contracts.hr_action_required', [
            'hrName'    => $notifiable->full_name,
            'candidate' => $candidate->full_name,
            'signUrl'   => $this->signUrl,
            'resignUrl' => $this->resignUrl,
        ])
            ->attachData($pdfBinary, "contract_{$this->contract->id}_candidate_signed.pdf", ['mime' => 'application/pdf']);
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'hr_action_required', 'contract_id' => $this->contract->id];
    }

      public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
                 'type' => 'hr_action_required', 'contract_id' => $this->contract->id
        ]);
    }

}
