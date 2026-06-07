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
            $table->string('jurisdiction', 100)->nullable();
            $table->integer('termination_notice_days')->default(30);
            $table->time('expected_check_in')->default('09:00:00');
            $table->time('expected_check_out')->default('17:00:00');
            $table->integer('sick_leave_days')->default(15);
            $table->integer('annual_leave_days')->default(14);
            $table->string('currency', 3)->default('SYP');
            $table->integer('grace_period')->default(15);
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
