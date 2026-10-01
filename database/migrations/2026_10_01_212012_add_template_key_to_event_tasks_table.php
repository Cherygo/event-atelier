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
        Schema::table('event_tasks', function (Blueprint $table) {
            $table->string('template_key', 100)->nullable();
            $table->unique(['event_id', 'template_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_tasks', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'template_key']);
            $table->dropColumn('template_key');
        });
    }
};
