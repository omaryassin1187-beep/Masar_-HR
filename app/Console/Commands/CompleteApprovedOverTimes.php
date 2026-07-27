<?php

namespace App\Console\Commands;

use App\Services\OverTimeService;
use Illuminate\Console\Command;

class CompleteApprovedOverTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:complete-approved-over-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function __construct(
        protected OverTimeService $overTimeService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->overTimeService->completeApprovedOverTimes();

        $this->info('Approved overtimes completed successfully.');

        return self::SUCCESS;
    }
}
