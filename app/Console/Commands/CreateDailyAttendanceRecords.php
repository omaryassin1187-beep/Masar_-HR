<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance_Leaves\Attendance;
use App\Services\AttendanceService;

class CreateDailyAttendanceRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-daily-attendance-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create an Attendance record for every active user day by day  ';

    /**
     * Execute the console command.
     */
        public function handle(AttendanceService $attendanceService): void
    {
        $today = now()->toDateString();

        // if (! $attendanceService->isWorkingDay($today)) {
        //     return;
        // }

        User::where('status', 'active')
            ->select('id')
            ->chunkById(500, function ($users) use ($today) {

                $rows = [];

                foreach ($users as $user) {
                    $rows[] = [
                        'user_id' => $user->id,
                        'date' => $today,
                        'status' => 'absent',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Attendance::insertOrIgnore($rows);
            });
    }
}
