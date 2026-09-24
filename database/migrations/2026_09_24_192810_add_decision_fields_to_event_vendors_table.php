<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_vendors', function (Blueprint $table): void {
            $table->string('status')->default('researching');
            $table->decimal('quote_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('quote_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('event_vendors', function (Blueprint $table): void {
            $table->dropColumn(['status', 'quote_amount', 'currency', 'quote_details']);
        });
    }
};
