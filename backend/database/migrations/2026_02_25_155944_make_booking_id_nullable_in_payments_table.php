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
            // Drop existing foreign key first
            $table->dropForeign(['booking_id']);
            
            // Make booking_id nullable
            $table->foreignId('booking_id')->nullable()->change();
            
            // Re-add foreign key constraint
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
        });

        // Add check constraint: cannot have both booking_id and hall_booking_id at the same time (drop first if exists)
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_booking_check');
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_booking_check CHECK (NOT (booking_id IS NOT NULL AND hall_booking_id IS NOT NULL))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop check constraint
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_booking_check');
            
            // Drop foreign key
            $table->dropForeign(['booking_id']);
            
            // Make booking_id not nullable again
            $table->foreignId('booking_id')->nullable(false)->change();
            
            // Re-add foreign key
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
        });
    }
};
