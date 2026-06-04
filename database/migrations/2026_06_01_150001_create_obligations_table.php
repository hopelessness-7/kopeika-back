<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('type', 32);

            $table->decimal('payment_amount', 15, 2);
            $table->unsignedTinyInteger('payment_day');

            $table->decimal('remaining_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();

            $table->string('lender')->nullable();
            $table->text('note')->nullable();

            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'payment_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligations');
    }
};
