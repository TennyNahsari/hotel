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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('hall_booking_id')->nullable()->after('booking_id')->constrained('hall_bookings')->onDelete('restrict');
            $table->index('hall_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['hall_booking_id']);
            $table->dropIndex(['hall_booking_id']);
            $table->dropColumn('hall_booking_id');
        });
    }
};
