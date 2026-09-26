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
        Schema::table('event_shares', function (Blueprint $table) {
            $table->boolean('show_progress')->default(false);
            $table->boolean('show_vendors')->default(false);
            $table->boolean('show_budget')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_shares', function (Blueprint $table) {
            $table->dropColumn(['show_progress', 'show_vendors', 'show_budget']);
        });
    }
};
