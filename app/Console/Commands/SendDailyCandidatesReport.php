<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\User;
use App\Notifications\candidate\DailyCandidatesReportNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDailyCandidatesReport extends Command
{
    protected $signature = 'candidates:daily-report';
    protected $description = 'Send daily report of new candidates to HR';

    public function handle()
    {
        $today = now()->startOfDay();

        $newCandidates = Candidate::where('created_at', '>=', $today)->get();

        if ($newCandidates->isEmpty()) {
            $this->info('No new candidates today.');
            return;
        }

        $hrUsers = User::role('HR')->get();

        Notification::send($hrUsers, new DailyCandidatesReportNotification(
            $newCandidates,
            $newCandidates->count()
        ));

        $this->info('Daily report sent to ' . $hrUsers->count() . ' HR users.');
    }
}
