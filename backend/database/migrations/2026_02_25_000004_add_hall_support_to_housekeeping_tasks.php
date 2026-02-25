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

        // Modify task_type enum to include hall_cleaning (PostgreSQL compatible)
        // Drop the old check constraint
        DB::statement("ALTER TABLE housekeeping_tasks DROP CONSTRAINT IF EXISTS housekeeping_tasks_task_type_check");
        
        // Add new check constraint with hall_cleaning option
        DB::statement("ALTER TABLE housekeeping_tasks ADD CONSTRAINT housekeeping_tasks_task_type_check CHECK (task_type IN ('cleaning', 'inspection', 'maintenance', 'hall_cleaning'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore task_type enum (PostgreSQL compatible)
        DB::statement("ALTER TABLE housekeeping_tasks DROP CONSTRAINT IF EXISTS housekeeping_tasks_task_type_check");
        DB::statement("ALTER TABLE housekeeping_tasks ADD CONSTRAINT housekeeping_tasks_task_type_check CHECK (task_type IN ('cleaning', 'inspection', 'maintenance'))");

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
