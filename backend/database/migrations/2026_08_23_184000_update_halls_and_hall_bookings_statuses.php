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
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable'))");

            DB::statement("ALTER TABLE hall_bookings DROP CONSTRAINT IF EXISTS hall_bookings_status_check");
            DB::statement("ALTER TABLE hall_bookings ADD CONSTRAINT hall_bookings_status_check CHECK (status::text IN ('pending', 'confirmed', 'checked_in', 'completed', 'cancelled'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'maintenance', 'unavailable'))");

            DB::statement("ALTER TABLE hall_bookings DROP CONSTRAINT IF EXISTS hall_bookings_status_check");
            DB::statement("ALTER TABLE hall_bookings ADD CONSTRAINT hall_bookings_status_check CHECK (status::text IN ('pending', 'confirmed', 'cancelled', 'completed'))");
        }
    }
};
