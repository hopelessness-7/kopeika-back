<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('bank', 32);
            $table->string('status', 32)->default('processing');
            $table->string('file_hash', 64)->nullable();

            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();

            $table->text('error_message')->nullable();
            $table->timestamp('imported_at')->nullable();

            $table->timestamp('confirmed_at')->nullable();
            $table->decimal('confirmed_balance', 15, 2)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_imports');
    }
};
