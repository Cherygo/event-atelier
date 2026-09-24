<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_vendors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 180);
            $table->string('category', 60);
            $table->string('contact_name', 120)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('website', 2048)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_vendors');
    }
};
