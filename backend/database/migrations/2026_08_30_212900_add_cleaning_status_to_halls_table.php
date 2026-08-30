<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'cleaning' to halls status check constraint.
     * HousekeepingController sets hall status to 'cleaning' when a cleaning task
     * is started/created, but the DB constraint didn't include this value.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable', 'cleaning'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            // Revert: first reset any 'cleaning' rows back to 'available'
            DB::statement("UPDATE halls SET status = 'available' WHERE status = 'cleaning'");
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable'))");
        }
    }
};
