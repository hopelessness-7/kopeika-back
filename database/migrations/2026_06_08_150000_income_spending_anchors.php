<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->boolean('is_spending_anchor')->default(false)->after('is_active');
            $table->index(['user_id', 'is_spending_anchor', 'is_active']);
        });

        if (Schema::hasColumn('user_settings', 'salary_day_of_month')) {
            $settings = DB::table('user_settings')
                ->whereNotNull('salary_day_of_month')
                ->get();

            foreach ($settings as $row) {
                $income = DB::table('incomes')
                    ->where('user_id', $row->user_id)
                    ->where('is_recurring', true)
                    ->where('day_of_month', $row->salary_day_of_month)
                    ->orderBy('id')
                    ->first();

                if ($income !== null) {
                    DB::table('incomes')
                        ->where('id', $income->id)
                        ->update(['is_spending_anchor' => true]);
                }
            }

            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn('salary_day_of_month');
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('salary_day_of_month')->nullable()->after('notification_mode');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_spending_anchor', 'is_active']);
            $table->dropColumn('is_spending_anchor');
        });
    }
};
