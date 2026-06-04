<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('received_at');
            $table->timestamps();

            $table->index(['user_id', 'received_at']);
        });

        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('bank');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('monthly_contribution', 15, 2)->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::table('bank_imports', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('file_hash');
            $table->string('storage_path')->nullable()->after('original_filename');
            $table->unsignedBigInteger('file_size')->nullable()->after('storage_path');
        });

        if (Schema::hasTable('user_settings')) {
            $rows = DB::table('user_settings')->get();

            foreach ($rows as $row) {
                DB::table('reconciliation_settings')->insert([
                    'user_id' => $row->user_id,
                    'import_interval_days' => $row->import_interval_days ?? 10,
                    'last_import_at' => $row->last_import_at,
                    'primary_anchor' => $row->primary_anchor ?? 'auto',
                    'salary_day_of_month' => $row->salary_day_of_month,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn([
                    'salary_day_of_month',
                    'salary_amount',
                    'import_interval_days',
                    'last_import_at',
                    'primary_anchor',
                    'buffer_amount',
                    'buffer_percent',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('salary_day_of_month')->nullable();
            $table->decimal('salary_amount', 15, 2)->nullable();
            $table->unsignedTinyInteger('import_interval_days')->default(10);
            $table->timestamp('last_import_at')->nullable();
            $table->string('primary_anchor', 16)->default('auto');
            $table->decimal('buffer_amount', 15, 2)->nullable();
            $table->decimal('buffer_percent', 5, 2)->nullable();
        });

        Schema::table('bank_imports', function (Blueprint $table) {
            $table->dropColumn(['original_filename', 'storage_path', 'file_size']);
        });

        Schema::dropIfExists('savings');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('reconciliation_settings');
    }
};
