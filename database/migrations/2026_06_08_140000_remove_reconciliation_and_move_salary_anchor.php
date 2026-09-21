<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('salary_day_of_month')->nullable()->after('notification_mode');
        });

        if (Schema::hasTable('reconciliation_settings')) {
            $rows = DB::table('reconciliation_settings')->get();

            foreach ($rows as $row) {
                DB::table('user_settings')
                    ->where('user_id', $row->user_id)
                    ->update(['salary_day_of_month' => $row->salary_day_of_month]);
            }
        }

        Schema::table('balance_snapshots', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_import_id');
        });

        Schema::dropIfExists('spend_period_summaries');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bank_imports');
        Schema::dropIfExists('reconciliation_settings');
    }

    public function down(): void
    {
        Schema::create('reconciliation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('import_interval_days')->default(10);
            $table->timestamp('last_import_at')->nullable();
            $table->string('primary_anchor', 16)->default('auto');
            $table->unsignedTinyInteger('salary_day_of_month')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bank', 32);
            $table->string('status', 16)->default('completed');
            $table->string('file_hash', 64)->nullable();
            $table->string('original_filename')->nullable();
            $table->string('storage_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_import_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 8);
            $table->decimal('amount', 15, 2);
            $table->timestamp('booked_at');
            $table->string('description')->nullable();
            $table->string('external_hash', 64);
            $table->timestamps();
        });

        Schema::create('spend_period_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_import_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('actual_spend', 15, 2)->default(0);
            $table->decimal('planned_spend', 15, 2)->default(0);
            $table->decimal('delta', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('balance_snapshots', function (Blueprint $table) {
            $table->foreignId('bank_import_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        if (Schema::hasColumn('user_settings', 'salary_day_of_month')) {
            $rows = DB::table('user_settings')->get();

            foreach ($rows as $row) {
                DB::table('reconciliation_settings')->insert([
                    'user_id' => $row->user_id,
                    'salary_day_of_month' => $row->salary_day_of_month,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn('salary_day_of_month');
            });
        }
    }
};
