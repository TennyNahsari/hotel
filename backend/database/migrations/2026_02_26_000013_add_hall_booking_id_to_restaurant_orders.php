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
        Schema::table('restaurant_orders', function (Blueprint $table) {
            // Make booking_id nullable since we can have hall_booking_id instead
            $table->foreignId('booking_id')->nullable()->change();
            
            // Add hall_booking_id
            $table->foreignId('hall_booking_id')->nullable()->after('booking_id')->constrained('hall_bookings')->onDelete('cascade');
            
            $table->index('hall_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropForeign(['hall_booking_id']);
            $table->dropIndex(['hall_booking_id']);
            $table->dropColumn('hall_booking_id');
            
            // Restore booking_id to not nullable
            $table->foreignId('booking_id')->nullable(false)->change();
        });
    }
};
