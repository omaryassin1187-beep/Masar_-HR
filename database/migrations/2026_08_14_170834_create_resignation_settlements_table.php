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
            // cascadeOnDelete هون مقبول لأنه سجل snapshot تابع بالكامل للاستقالة، مش سجل مستقل له كيان خاص

            $table->decimal('unused_leave_days', 5, 2)->default(0);
            $table->decimal('unused_leave_amount', 10, 2)->default(0);

            $table->decimal('notice_period_amount', 10, 2)->nullable();

            $table->decimal('end_of_service_gratuity', 10, 2)->nullable();

            $table->decimal('base_salary_amount', 10, 2)->nullable();
            // (راتب أيام الدوام الفعلية)

            $table->decimal('total_settlement_amount', 10, 2)->nullable();
            // null لحد ما يتوفر base_salary_amount

            $table->boolean('is_finalized')->default(false);
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
