<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spend_period_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_import_id')->nullable()->constrained()->nullOnDelete();

            $table->date('period_from');
            $table->date('period_to');

            $table->decimal('planned_spend', 15, 2)->default(0);
            $table->decimal('actual_spend', 15, 2)->default(0);
            $table->decimal('delta', 15, 2)->default(0);

            $table->decimal('daily_limit_after', 15, 2)->nullable();
            $table->date('limit_valid_until')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'period_to']);
            $table->unique(['bank_import_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spend_period_summaries');
    }
};
