<?php
// app/Console/Commands/FinalizeDueResignations.php

namespace App\Console\Commands;

use App\Services\ResignationService;
use Illuminate\Console\Command;

class FinalizeDueResignations extends Command
{
    protected $signature = 'resignations:finalize-due';
    protected $description = 'Terminate contracts for employees who have reached their last working day within the notice period';

    public function handle(ResignationService $service): int
    {
        $count = $service->finalizeDueNoticePeriods();
        $this->info("Terminated {$count} resignation request(s).");
        return self::SUCCESS;
    }
}
