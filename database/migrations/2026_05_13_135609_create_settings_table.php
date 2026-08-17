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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('probation_period_days')->default(90);
            $table->json('weekend_days')->default('["friday","saturday"]');
            $table->string('jurisdiction', 50)->nullable();
            $table->integer('termination_notice_days')->default(30);
            $table->time('expected_check_in')->default('09:00:00');
            $table->time('expected_check_out')->default('17:00:00');
            $table->integer('sick_leave_days')->default(10);
            $table->integer('annual_leave_days')->default(14);
            $table->string('currency', 3)->default('SYP');
            $table->integer('grace_period')->default(15);
            $table->decimal('company_latitude', 10, 7)->default(33.5154600);
            $table->decimal('company_longitude', 10, 7)->default(36.2788800);
            $table->integer('allowed_radius')->default(200); // بالمتر

            $table->decimal('eval_task_quality_weight', 4, 2)->default(0.40);
            $table->decimal('eval_task_ontime_weight', 4, 2)->default(0.30);
            $table->decimal('eval_attendance_weight', 4, 2)->default(0.30);
            $table->decimal('eval_salary_increase_threshold', 5, 2)->default(95.00);
            $table->unsignedTinyInteger('eval_min_tenure_days')->default(30);

            $table->integer('end_of_service_months_per_year' )->default(1); // شهر أجر واحد عن كل سنة خدمة كاملة (افتراضي)


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
