<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationMetric extends Model
{
    protected $fillable = [
        'evaluation_id',
        'working_days_count',
        'attendance_rate',
        'late_rate',
        'absence_rate',
        'tasks_submitted_count',
        'on_time_rate',
        'avg_task_score',
        'overdue_tasks_count',
    ];

    protected $casts = [
        'attendance_rate' => 'decimal:2',
        'late_rate'       => 'decimal:2',
        'absence_rate'    => 'decimal:2',
        'on_time_rate'    => 'decimal:2',
        'avg_task_score'  => 'decimal:2',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(PerformanceEvaluation::class, 'evaluation_id');
    }
}
