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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignid('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignid('job_posting_id')->constrained()->cascadeOnDelete();
            $table->decimal('hour_price', 10, 2);
            $table->date('start_date');
            $table->json('weekend_days'); // e.g., ["Saturday", "Sunday"]
            $table->integer('working_hour_per_day');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
