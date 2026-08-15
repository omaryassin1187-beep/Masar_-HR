<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resignations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->enum('type', ['with_notice', 'immediate']);

            $table->text('reason')->nullable();
            $table->date('last_working_day')->nullable();

            $table->enum('status', [
                'submitted',
                'manager_notified',
                'contract_terminated',
                'cancelled',
            ])->default('submitted');

            $table->enum('hr_classification', [
                'mutual_consent',
                'breach_by_company',
                'breach_by_employee',
            ])->nullable();
            $table->text('hr_classification_notes')->nullable();
            $table->foreignId('classified_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('classified_at')->nullable();

            $table->enum('notice_period_treatment', [
                'not_applicable', //  immediate بالتراضي
                'compensate',     //إخلال من الشركة
                'deduct',         // إخلال من الموظف
            ])->default('not_applicable');

            $table->timestamp('manager_notified_at')->nullable();

            $table->foreignId('contract_id')->nullable()->constrained('contracts')->restrictOnDelete();
            $table->timestamp('contract_terminated_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resignations');
    }
};
