<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Snapshot
            $table->decimal('hourly_rate', 10, 2);

            $table->unsignedTinyInteger('working_hours_per_day');
            $table->unsignedTinyInteger('working_days');

            // Salary
            $table->decimal('base_salary', 12, 2);

            // Additions
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('incentive_amount', 12, 2)->default(0);

            // Deductions
            $table->decimal('deductions_amount', 12, 2)->default(0);

            $table->unsignedTinyInteger('unpaid_leave_days')->default(0);
            $table->decimal('unpaid_leave_amount', 12, 2)->default(0);

            // Totals
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('net_salary', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['payroll_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
