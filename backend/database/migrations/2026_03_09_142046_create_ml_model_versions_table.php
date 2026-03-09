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
        Schema::create('ml_model_versions', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 50); // 'room_demand', 'hall_peak', 'menu_popularity'
            $table->string('version', 20); // timestamp-based version
            $table->decimal('accuracy', 5, 4)->nullable(); // e.g., 0.8425
            $table->integer('trained_samples')->nullable();
            $table->string('file_path', 255);
            $table->bigInteger('file_size')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('trained_at');
            $table->timestamps();
            
            $table->index(['model_name', 'is_active']);
            $table->index('trained_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_model_versions');
    }
};
