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
        Schema::table('laundry_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('laundry_orders', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'delivered', 'cancelled'])
                      ->default('pending')
                      ->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laundry_orders', function (Blueprint $table) {
            if (Schema::hasColumn('laundry_orders', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
