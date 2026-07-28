<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('subject_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description');

            // تُحسب تلقائياً بالـ Service من دور subject_id — لا تُدخل من المستخدم
            $table->enum('route_type', ['against_manager', 'against_employee']);

            $table->enum('status', ['pending', 'under_review', 'resolved', 'rejected'])
                ->default('pending');

            $table->text('hr_note')->nullable();

            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('author_id');
            $table->index('subject_id');
        });
    }
////
//test
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
