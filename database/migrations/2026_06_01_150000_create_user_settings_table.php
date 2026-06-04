<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('salary_day_of_month')->nullable();
            $table->decimal('salary_amount', 15, 2)->nullable();

            $table->unsignedTinyInteger('import_interval_days')->default(10); // 7 | 10 | 14
            $table->timestamp('last_import_at')->nullable();
            $table->timestamp('last_check_in_at')->nullable();

            $table->string('primary_anchor', 16)->default('auto');
            $table->decimal('buffer_amount', 15, 2)->nullable();
            $table->decimal('buffer_percent', 5, 2)->nullable();

            $table->string('notification_mode', 32)->default('normal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
