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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'hotel_branch_id')) {
                $table->foreignId('hotel_branch_id')
                    ->nullable()
                    ->after('role_id')
                    ->constrained('hotel_branches')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'hotel_branch_id')) {
                $table->dropForeign(['hotel_branch_id']);
                $table->dropColumn('hotel_branch_id');
            }
        });
    }
};
