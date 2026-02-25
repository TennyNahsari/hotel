<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Make booking_id nullable (payment can be for room OR hall)
            $table->foreignId('booking_id')->nullable()->change();
            
            // Add hall_booking_id
            $table->foreignId('hall_booking_id')->nullable()->after('booking_id')->constrained('hall_bookings')->onDelete('restrict');
            $table->index('hall_booking_id');
            
            // Add check constraint: must have either booking_id or hall_booking_id
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_booking_check CHECK ((booking_id IS NOT NULL AND hall_booking_id IS NULL) OR (booking_id IS NULL AND hall_booking_id IS NOT NULL))');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop check constraint
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_booking_check');
            
            // Drop hall_booking_id
            $table->dropForeign(['hall_booking_id']);
            $table->dropIndex(['hall_booking_id']);
            $table->dropColumn('hall_booking_id');
            
            // Revert booking_id to not nullable
            $table->foreignId('booking_id')->nullable(false)->change();
        });
    }
};
