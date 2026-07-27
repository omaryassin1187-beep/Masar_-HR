<?php

namespace App\Services\Evaluation;

use App\Models\Attendance_Leaves\Attendance;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;

class EvaluationKPIService
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function calculate(User $employee, Carbon $start, Carbon $end): array
    {
        return [
            ...$this->calculateAttendanceMetrics($employee, $start, $end),
            ...$this->calculateTaskMetrics($employee, $start, $end),
        ];
    }

    protected function calculateAttendanceMetrics(User $employee, Carbon $start, Carbon $end): array
    {
        $workingDays = $this->attendanceService->countWorkingDays($start, $end);

        if ($workingDays === 0) {
            return [
                'working_days_count' => 0,
                'attendance_rate'    => 0,
                'late_rate'          => 0,
                'absence_rate'       => 0,
            ];
        }

        $records = Attendance::where('user_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentCount = $records->whereIn('status', ['present', 'late', 'leave'])->count();
        $lateCount    = $records->where('status', 'late')->count();
        $absentCount  = $workingDays - $presentCount;

        return [
            'working_days_count' => $workingDays,
            'attendance_rate'     => round(($presentCount / $workingDays) * 100, 2),
            'late_rate'           => round(($lateCount / $workingDays) * 100, 2),
            'absence_rate'        => round(($absentCount / $workingDays) * 100, 2),
        ];
    }

    protected function calculateTaskMetrics(User $employee, Carbon $start, Carbon $end): array
    {
        $tasks = Task::query()
            ->where('assigned_to', $employee->id)
            ->whereBetween('due_date', [$start, $end])
            ->get();

        $submittedCount = $tasks->whereNotNull('submitted_at')->count();
        $overdueCount   = $tasks->filter(fn (Task $t) => $t->isOverdue())->count();

        $onTimeCount = $tasks->filter(fn (Task $t) => $t->wasSubmittedOnTime())->count();
        $onTimeRate  = $submittedCount > 0
            ? round(($onTimeCount / $submittedCount) * 100, 2)
            : 0;

        $scored   = $tasks->whereNotNull('score');
        $avgScore = $scored->count() > 0
            ? round((float) $scored->avg('score'), 2)
            : 0;

        return [
            'tasks_submitted_count' => $submittedCount,
            'on_time_rate'          => $onTimeRate,
            'avg_task_score'        => $avgScore,
            'overdue_tasks_count'   => $overdueCount,
        ];
    }

    public function computeAutomatedScore(array $metrics): float
    {
        $setting = Setting::instance();

        $score = ($metrics['avg_task_score'] * (float) $setting->eval_task_quality_weight)
            + ($metrics['on_time_rate'] * (float) $setting->eval_task_ontime_weight)
            + ($metrics['attendance_rate'] * (float) $setting->eval_attendance_weight);

        return round($score, 2);
    }

    public function resolveRatingLabel(float $score): string
    {
        // ✅ التعديل: تصنيفات عربية
        return match (true) {
            $score >= 90 => 'ممتاز',
            $score >= 75 => 'جيد',
            $score >= 60 => 'متوسط',
            default      => 'ضعيف',
        };
    }
}
