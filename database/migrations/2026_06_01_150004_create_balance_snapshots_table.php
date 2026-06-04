<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_import_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 15, 2);
            $table->string('source', 32);
            $table->timestamp('recorded_at');

            $table->string('note')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_snapshots');
    }
};
