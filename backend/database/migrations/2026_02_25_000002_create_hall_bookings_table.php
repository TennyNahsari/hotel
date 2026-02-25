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
        Schema::create('hall_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 50)->unique(); // HB-YYYYMMDD-XXXX
            $table->foreignId('hall_id')->constrained('halls')->onDelete('restrict');
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->string('customer_name', 100);
            $table->string('customer_email', 100);
            $table->string('customer_phone', 20);
            $table->string('customer_company', 100)->nullable();
            $table->string('event_name', 200);
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('duration_hours', 5, 2); // Calculated
            $table->integer('attendees');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('booked_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            // Indexes
            $table->index(['event_date', 'start_time', 'end_time']);
            $table->index(['hall_id', 'event_date', 'status']);
            $table->index('guest_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_bookings');
    }
};
