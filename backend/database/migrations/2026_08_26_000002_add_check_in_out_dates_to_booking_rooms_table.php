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
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->date('check_in_date')->nullable()->after('room_id');
            $table->date('check_out_date')->nullable()->after('check_in_date');
        });

        // Backfill check_in_date & check_out_date from parent bookings table (Database-agnostic)
        $checkInsMap = DB::table('bookings')->pluck('check_in_date', 'id');
        $checkOutsMap = DB::table('bookings')->pluck('check_out_date', 'id');

        DB::table('booking_rooms')->get()->each(function ($item) use ($checkInsMap, $checkOutsMap) {
            if (isset($checkInsMap[$item->booking_id])) {
                DB::table('booking_rooms')
                    ->where('id', $item->id)
                    ->update([
                        'check_in_date' => $checkInsMap[$item->booking_id],
                        'check_out_date' => $checkOutsMap[$item->booking_id],
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropColumn(['check_in_date', 'check_out_date']);
        });
    }
};
