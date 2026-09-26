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
        Schema::table('event_expenses', function (Blueprint $table) {
            $table->bigInteger('actual_minor')->nullable();
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_expenses', function (Blueprint $table) {
            $table->dropColumn(['actual_minor', 'due_date']);
        });
    }
};
