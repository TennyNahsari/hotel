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
            DB::statement("ALTER TABLE rooms DROP CONSTRAINT IF EXISTS rooms_status_check");
            DB::statement("ALTER TABLE rooms ADD CONSTRAINT rooms_status_check CHECK (status::text IN ('available', 'booked', 'occupied', 'dirty', 'cleaning', 'out_of_order'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'booked', 'occupied', 'dirty', 'cleaning', 'out_of_order') DEFAULT 'available'");
        } else {
            // SQLite or fallback
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('status')->default('available')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE rooms DROP CONSTRAINT IF EXISTS rooms_status_check");
            DB::statement("ALTER TABLE rooms ADD CONSTRAINT rooms_status_check CHECK (status::text IN ('available', 'occupied', 'dirty', 'cleaning', 'out_of_order'))");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'dirty', 'cleaning', 'out_of_order') DEFAULT 'available'");
        }
    }
};
