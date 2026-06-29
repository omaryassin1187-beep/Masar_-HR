<?php
// app/Notifications/SignContractRequest.php
namespace App\Notifications\contracts;

use App\Models\Offer;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignContractRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Offer $offer,
        private readonly string $signedUrl,
        private readonly bool $isResend = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $candidate = $this->offer->candidate;
        $contract = $this->offer->contract;

        if ($contract && $contract->candidate_signed_at) {
            return (new MailMessage)
                ->subject('📄 ' . config('app.name') . ' — Already Signed')
                ->view('emails.already_signed', [
                    'candidateName' => $candidate->full_name,
                    'offerId' => $this->offer->id,
                ]);
        }
        $pdfBinary = Pdf::loadView('contracts.preview', [
            'offer'         => $this->offer,
            'user'          => $candidate,
            'jobTitle'      => $this->offer->jobPosting->requisition->job_title,
            'endDate'       => $this->offer->start_date->copy()->addYear(),
            'probationDays' => Setting::instance()->probation_period_days,
        ])->setPaper('a4', 'portrait')->output();

        $mail = (new MailMessage)->subject(
            $this->isResend
                ? '📄 MasarHR —Re-sign Required'
                : '📄 MasarHR — Sign Your Contract Now'
        );

        if ($this->isResend) {
            $mail->line('⚠️HR requested re-signing this contract');
        }

        return $mail
            ->view('contracts.sign_contract', [
                'candidateName' => $candidate->full_name,
                'signedUrl'     => $this->signedUrl,
                'offerId'       => $this->offer->id,
                'isResend'      => $this->isResend,
            ])
            ->attachData($pdfBinary, "contract_{$this->offer->id}.pdf", ['mime' => 'application/pdf']);
    }
}
