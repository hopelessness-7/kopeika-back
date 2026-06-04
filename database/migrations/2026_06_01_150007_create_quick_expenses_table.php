<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->timestamp('noted_at');
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'noted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_expenses');
    }
};
