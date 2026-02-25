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
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            // Add hall_id column
            $table->foreignId('hall_id')->nullable()->after('room_id')->constrained('halls')->onDelete('restrict');
            $table->index('hall_id');
        });

        // Modify room_id to be nullable
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->change();
        });

        // Modify task_type enum to include hall_cleaning
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN task_type ENUM('cleaning', 'inspection', 'maintenance', 'hall_cleaning') DEFAULT 'cleaning'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore task_type enum
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN task_type ENUM('cleaning', 'inspection', 'maintenance') DEFAULT 'cleaning'");

        // Restore room_id to not nullable
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable(false)->change();
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropForeign(['hall_id']);
            $table->dropIndex(['hall_id']);
            $table->dropColumn('hall_id');
        });
    }
};
