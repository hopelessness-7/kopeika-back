<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('description');
            $table->unsignedTinyInteger('day_of_month')->nullable()->after('is_recurring');
            $table->boolean('is_active')->default(true)->after('day_of_month');

            $table->index(['user_id', 'is_recurring', 'is_active']);
        });

        Schema::table('obligation_payments', function (Blueprint $table) {
            $table->index(['user_id', 'paid_at']);
            $table->index(['obligation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('obligation_payments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'paid_at']);
            $table->dropIndex(['obligation_id', 'status']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_recurring', 'is_active']);
            $table->dropColumn(['is_recurring', 'day_of_month', 'is_active']);
        });
    }
};
