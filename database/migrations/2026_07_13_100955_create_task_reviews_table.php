<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('score'); // 0 → 100
            $table->text('comment')->nullable();
            $table->enum('status', ['approved', 'rejected']);
            $table->timestamps();

            $table->index('task_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reviews');
    }
};
