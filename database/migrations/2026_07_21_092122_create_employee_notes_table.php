<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();   // Employee who owns the note
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete(); // Author (Manager or HR)
            $table->enum('type', ['positive', 'negative', 'goal', 'general'])->default('general');
            $table->text('content');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_notes');
    }
};
