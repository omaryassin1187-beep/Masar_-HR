
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title', 200);
            $table->text('content');

            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');

            $table->enum('target_audience', ['all', 'department', 'managers']);

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])
                ->default('draft');

            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index(['status', 'expires_at']);

            // الموظف بيشوف الإعلانات الفعالة المستهدفة له
            $table->index(['target_audience', 'department_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
