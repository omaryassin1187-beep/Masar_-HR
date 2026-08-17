<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resignation_settlements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('resignation_id')->constrained()->cascadeOnDelete();

            $table->decimal('annual_leave_days', 5, 2)->default(0);
            $table->decimal('annual_leave_amount', 10, 2)->default(0);
            $table->decimal('sick_leave_days', 5, 2)->default(0);
            $table->decimal('sick_leave_amount', 10, 2)->default(0);

            $table->decimal('notice_period_amount', 10, 2)->nullable();
            // Magnitude فقط — الاتجاه (تعويض/خصم) من resignations.notice_period_treatment

            $table->decimal('end_of_service_gratuity', 10, 2)->nullable();

            $table->decimal('total_compensation_amount', 10, 2)->default(0);
            // مجموع الثلاث فقرات فوق (بدون أي علاقة بالراتب الأساسي)

            $table->timestamp('emailed_at')->nullable();

            $table->timestamps();

            $table->unique('resignation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignation_settlements');
    }
};
