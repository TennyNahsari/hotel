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
        Schema::table('room_types', function (Blueprint $table) {
            // Check if column exists before dropping
            if (Schema::hasColumn('room_types', 'weekend_price')) {
                $table->dropColumn('weekend_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('weekend_price', 12, 2)->nullable()->after('base_price');
        });
    }
};
