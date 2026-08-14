<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('termination_requests', function (Blueprint $table) {
            $table->id();

            // الموظف المراد إنهاء خدمته
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // عقد الموظف
            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnDelete();

            // الشخص الذي أنشأ طلب الطرد
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // الجهة التي أنشأت الطلب
            $table->enum('created_by_role', [
                'HR',
                'manager',
            ]);

            // نوع إنهاء الخدمة
            $table->enum('type', [
                'standard',
                'immediate',
            ]);

            // تاريخ إنهاء الخدمة
            $table->date('termination_date');

            // آخر يوم عمل للموظف
            $table->date('last_working_day');

            // هل أصبح الطلب جاهزًا ليصل إلى الـ Admin؟
            $table->boolean('ready_for_admin')
                ->default(false);

            // حالة الطلب
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
            ])->default('draft');

            $table->unsignedInteger('notice_period_days')
                ->default(30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('termination_requests');
    }
};