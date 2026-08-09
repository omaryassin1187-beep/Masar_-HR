<?php

namespace App\Console\Commands;

use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateMonthlyPayroll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-monthly-payroll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create payroll draft for previous month';

    /**
     * Execute the console command.
     */
    public function handle(PayrollService $service): int
{
    $service->createMonthlyDraft();

    return self::SUCCESS;
}
}
