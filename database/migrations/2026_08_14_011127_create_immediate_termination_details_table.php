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
        Schema::create('immediate_termination_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('termination_id')
                ->constrained('termination_requests')
                ->cascadeOnDelete();

            $table->enum('subtype', [
                'misconduct',
                'company_composition',
                'mutual_agreement',
            ]);

            $table->decimal('compensation_amount', 12, 2)->nullable();

            $table->text('legal_reason')->nullable();

            $table->string('documents_path')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immediate_termination_details');
    }
};
