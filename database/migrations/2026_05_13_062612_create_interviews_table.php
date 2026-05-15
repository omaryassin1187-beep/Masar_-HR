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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignid('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignid('job_posting_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date_interview');
            $table->enum('location_type', ['online', 'on_site'])->default('online');
            $table->enum('status', ['scheduled', 'done', 'cancelled'])->default('scheduled');
            $table->string('location_details')->nullable(); //e.g., Zoom link or office address
            $table->text('notes')->nullable();
            $table->enum('rate', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
