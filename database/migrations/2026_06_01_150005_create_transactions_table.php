<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_import_id')->constrained()->cascadeOnDelete();

            $table->timestamp('booked_at');
            $table->string('direction', 8)->default('out'); // out = расход, in = приход
            $table->decimal('amount', 15, 2); // всегда положительное абсолютное значение
            $table->string('description');
            $table->string('external_hash', 64)->nullable();
            $table->string('category_guess')->nullable();

            $table->timestamps();

            $table->index(['bank_import_id', 'booked_at']);
            $table->unique(['bank_import_id', 'external_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
