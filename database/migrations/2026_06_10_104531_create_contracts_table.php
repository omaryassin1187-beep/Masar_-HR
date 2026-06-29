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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->decimal('hour_price', 10, 2);
            $table->integer('working_hours_per_day');
            $table->json('weekend_days');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('probation_period_days')->default(50);
            $table->integer('termination_notice_days');
            $table->string('jurisdiction');
            $table->string('candidate_signature_path')->nullable();
            $table->timestamp('candidate_signed_at')->nullable();
            $table->string('hr_signature_path')->nullable();
            $table->timestamp('hr_signed_at')->nullable();
            $table->date('signed_at')->nullable();
            $table->enum('status', ['active', 'probation', 'expired', 'non_renewable',
            'awaiting_hr_signature','awaiting_candidate_signature'])->default('awaiting_hr_signature');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
