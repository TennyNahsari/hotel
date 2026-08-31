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
        DB::statement('ALTER TABLE laundry_orders ALTER COLUMN booking_id DROP NOT NULL');

        Schema::table('laundry_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('laundry_orders', 'hall_booking_id')) {
                $table->foreignId('hall_booking_id')->nullable()->after('booking_id')->constrained('hall_bookings')->onDelete('cascade');
                $table->index('hall_booking_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laundry_orders', function (Blueprint $table) {
            if (Schema::hasColumn('laundry_orders', 'hall_booking_id')) {
                $table->dropForeign(['hall_booking_id']);
                $table->dropColumn('hall_booking_id');
            }
        });

        DB::statement('ALTER TABLE laundry_orders ALTER COLUMN booking_id SET NOT NULL');
    }
};
