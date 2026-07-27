<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_metrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_id')
                ->constrained('performance_evaluations')
                ->cascadeOnDelete();
                //

            $table->unsignedSmallInteger('working_days_count')->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('late_rate', 5, 2)->default(0);
            $table->decimal('absence_rate', 5, 2)->default(0);

            $table->unsignedInteger('tasks_submitted_count')->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(0);
            $table->decimal('avg_task_score', 5, 2)->default(0);
            $table->unsignedInteger('overdue_tasks_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_metrics');
    }
};
