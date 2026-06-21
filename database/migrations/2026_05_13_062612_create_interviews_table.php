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
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewed_by')->constrained('users'); // المدير المسؤول
            $table->dateTime('scheduled_at');
            $table->enum('location_type', ['online', 'on_site'])->default('online');
            $table->string('location_details')->nullable();
            $table->enum('status', ['scheduled', 'done', 'cancelled'])->default('scheduled');
            $table->unsignedTinyInteger('rate')->nullable();   // 1-10
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('rank')->nullable();  // ترتيب المدير النهائي
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
