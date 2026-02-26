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
        // Change enum to add 'package' category
        DB::statement("ALTER TABLE menu_items MODIFY COLUMN category ENUM('food', 'beverage', 'snack', 'package') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE menu_items MODIFY COLUMN category ENUM('food', 'beverage', 'snack') NOT NULL");
    }
};
