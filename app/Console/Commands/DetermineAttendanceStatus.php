<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceService;
use App\Models\Attendance_Leaves\Attendance;
use App\Models\Setting;
class DetermineAttendanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:determine-attendance-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set user status for every day (present,leave,late,absent';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $attendanceService): void
    {
        $settings = Setting::instance();
        Attendance::with('user')
            ->whereDate('date', today())
            ->chunkById(500, function ($attendances)
                use ($attendanceService,$settings) {

                foreach ($attendances as $attendance) {

                    $attendance->update(
                        $attendanceService
                            ->resolveAttendance($attendance,$settings)
                    );
                }
            });
    }
}
