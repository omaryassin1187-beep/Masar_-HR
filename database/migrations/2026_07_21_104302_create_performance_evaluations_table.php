<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('manager_id')->constrained('users')->restrictOnDelete();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter'); // 1..4

            $table->date('period_start');
            $table->date('period_end');

            $table->decimal('automated_score', 5, 2)->nullable();

            $table->enum('behavioral_rating', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->text('manager_notes')->nullable();
            $table->json('next_quarter_goals')->nullable();

            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('rating_label', 20)->nullable();

            $table->enum('status', ['pending_manager', 'pending_hr_review', 'approved'])
                ->default('pending_manager');

            $table->text('hr_notes')->nullable();
            $table->foreignId('hr_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->timestamp('salary_increase_notified_at')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'year', 'quarter']);
            $table->index(['manager_id', 'status']);
            $table->index(['year', 'quarter', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
