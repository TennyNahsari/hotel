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
        Schema::table('hall_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('hall_bookings', 'deposit_amount')) {
                $table->decimal('deposit_amount', 12, 2)->default(0)->after('total_amount');
            }
        });

        \Illuminate\Support\Facades\DB::statement("UPDATE hall_bookings SET deposit_amount = total_amount * 0.5 WHERE (deposit_amount IS NULL OR deposit_amount = 0) AND (booked_by IS NULL OR notes LIKE '%Website%')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hall_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('hall_bookings', 'deposit_amount')) {
                $table->dropColumn('deposit_amount');
            }
        });
    }
};
