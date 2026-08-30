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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_due_at')) {
                $table->timestamp('payment_due_at')->nullable()->after('deposit_amount');
            }
        });

        Schema::table('hall_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('hall_bookings', 'payment_due_at')) {
                $table->timestamp('payment_due_at')->nullable()->after('total_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'payment_due_at')) {
                $table->dropColumn('payment_due_at');
            }
        });

        Schema::table('hall_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('hall_bookings', 'payment_due_at')) {
                $table->dropColumn('payment_due_at');
            }
        });
    }
};
