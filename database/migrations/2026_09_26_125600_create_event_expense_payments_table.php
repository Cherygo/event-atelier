<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_expense_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_expense_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_minor');
            $table->date('paid_on');
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['event_expense_id', 'paid_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_expense_payments');
    }
};
