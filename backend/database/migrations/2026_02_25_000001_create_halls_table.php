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
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('hall_type', 50); // Meeting Room Small, Ballroom, etc.
            $table->string('floor', 20)->nullable();
            $table->integer('capacity'); // Maximum capacity
            $table->decimal('area_sqm', 10, 2)->nullable(); // Area in square meters
            $table->decimal('price_per_hour', 12, 2); // Hourly rate
            $table->json('facilities')->nullable(); // JSON: {av_equipment: [], furniture: [], tech: []}
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['available', 'maintenance', 'unavailable'])->default('available');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('hall_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }
};
