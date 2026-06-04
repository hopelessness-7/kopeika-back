<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('obligation_id')->constrained()->cascadeOnDelete();

            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->string('status', 16)->default('planned');

            $table->timestamp('paid_at')->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique(['obligation_id', 'due_date']);
            $table->index(['user_id', 'due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligation_payments');
    }
};
