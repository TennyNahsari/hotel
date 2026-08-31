<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_booking_check');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_booking_check CHECK (NOT (booking_id IS NOT NULL AND hall_booking_id IS NOT NULL))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_booking_check');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_booking_check CHECK ((booking_id IS NOT NULL AND hall_booking_id IS NULL) OR (booking_id IS NULL AND hall_booking_id IS NOT NULL))');
        }
    }
};
