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
        // PostgreSQL: Drop old constraint and add new one with 'package'
        DB::statement("ALTER TABLE menu_items DROP CONSTRAINT IF EXISTS menu_items_category_check");
        DB::statement("ALTER TABLE menu_items ADD CONSTRAINT menu_items_category_check CHECK (category IN ('food', 'beverage', 'snack', 'package'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE menu_items DROP CONSTRAINT IF EXISTS menu_items_category_check");
        DB::statement("ALTER TABLE menu_items ADD CONSTRAINT menu_items_category_check CHECK (category IN ('food', 'beverage', 'snack'))");
    }
};
