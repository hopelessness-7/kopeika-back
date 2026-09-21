<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('check_in_streak_weeks')->default(0)->after('last_check_in_at');
            $table->decimal('buffer_amount', 12, 2)->nullable()->after('notification_mode');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn(['check_in_streak_weeks', 'buffer_amount']);
        });
    }
};
