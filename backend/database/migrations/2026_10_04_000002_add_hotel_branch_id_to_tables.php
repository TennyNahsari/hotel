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
        $tables = [
            'room_types',
            'rooms',
            'halls',
            'bookings',
            'hall_bookings',
            'housekeeping_tasks',
            'restaurant_orders',
            'laundry_orders',
            'settings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'hotel_branch_id')) {
                        $table->foreignId('hotel_branch_id')
                            ->nullable()
                            ->after('id')
                            ->constrained('hotel_branches')
                            ->nullOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'room_types',
            'rooms',
            'halls',
            'bookings',
            'hall_bookings',
            'housekeeping_tasks',
            'restaurant_orders',
            'laundry_orders',
            'settings',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'hotel_branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['hotel_branch_id']);
                    $table->dropColumn('hotel_branch_id');
                });
            }
        }
    }
};
