<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Salary\Deduction;
use App\Models\Attendance_Leaves\Attendance;

class CreateAutoDeductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-auto-deductions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a deduction for every late or absent employee today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $attendances = Attendance::whereDate('date', $today)->get();

        foreach ($attendances as $attendance) {

            $reasons = [];

            if ($attendance->status === 'late') {
                $reasons[] = 'late';
            }

            if ($attendance->status === 'absent') {
                $reasons[] = 'absent';
            }

            if ($attendance->early_leave_minutes > 0) {
                $reasons[] = 'early leave';
            }

            foreach ($reasons as $reason) {

                Deduction::firstOrCreate(
                    [
                        'referance_id' => $attendance->id,
                        'reason' => $reason,
                    ],
                    [
                        'user_id' => $attendance->user_id,
                        'date' => $attendance->date,
                        'amount' => 12 //$this->calculateAmount($attendance, $reason),
                    ]
                );
            }
        }

        $this->info('Attendance deductions processed successfully.');

        return Command::SUCCESS;
    }
}
