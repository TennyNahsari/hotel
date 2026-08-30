<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'dirty' status to halls table constraint.
     * Hall flow: complete → dirty (pending housekeeping) → available (after cleaning task done).
     * This mirrors the existing room flow: checkout → dirty → available.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable', 'cleaning', 'dirty'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("UPDATE halls SET status = 'available' WHERE status IN ('cleaning', 'dirty')");
            DB::statement("ALTER TABLE halls DROP CONSTRAINT IF EXISTS halls_status_check");
            DB::statement("ALTER TABLE halls ADD CONSTRAINT halls_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'maintenance', 'unavailable'))");
        }
    }
};
