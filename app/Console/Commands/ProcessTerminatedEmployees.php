<?php

namespace App\Console\Commands;

use App\Mail\TerminationCompensationMail;
use App\Models\Termination\TerminationRequest;
use App\Services\TerminationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessTerminatedEmployees extends Command
{
    protected $signature = 'termination:process';

    protected $description =
        'Process approved standard terminations whose last working day has arrived';

    public function __construct(
        protected TerminationService $terminationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        TerminationRequest::query()
            ->with([
                'user',
                'immediateTerminationDetail',
            ])
            ->where('type', 'standard')
            ->where('status', 'approved')
            ->whereDate('last_working_day', '<=', today())
            ->whereHas('user', function ($query) {
                $query->where('status', 'active');
            })
            ->chunkById(100, function ($terminationRequests) {

                foreach ($terminationRequests as $terminationRequest) {

                    DB::transaction(function () use ($terminationRequest) {

                        $employee = $terminationRequest->user;

                        /*
                         * Employee may have already been processed
                         * by another process.
                         */
                        if ($employee->status !== 'active') {
                            return;
                        }

                        /*
                         * Calculate unused leave compensation
                         * before changing employee status.
                         */
                        $leaveCompensation =
                            $this->terminationService
                                ->calculateUnusedLeaveCompensation($employee);

                        /*
                         * Send compensation email.
                         *
                         * Standard termination does not have
                         * immediate termination compensation.
                         */
                        Mail::to($employee->email)
                            ->send(
                                new TerminationCompensationMail(
                                    $terminationRequest,
                                    $leaveCompensation,
                                    null
                                )
                            );

                        /*
                         * Employee's last working day has arrived.
                         */
                        $employee->update([
                            'status' => 'inactive',
                        ]);
                    });
                }
            });

        $this->info(
            'Approved standard terminations processed successfully.'
        );

        return self::SUCCESS;
    }
}