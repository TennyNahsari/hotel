<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HallBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'hall_id',
        'guest_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'event_name',
        'event_date',
        'start_time',
        'end_time',
        'duration_hours',
        'attendees',
        'total_amount',
        'status',
        'special_requests',
        'notes',
        'booked_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'duration_hours' => 'decimal:2',
        'attendees' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    protected $appends = ['booking_type'];

    /**
     * Get the hall for this booking.
     */
    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Get the guest for this booking.
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the user who created this booking.
     */
    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    /**
     * Get the payments for this booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'hall_booking_id');
    }

    /**
     * Get booking type attribute (for unified display).
     */
    public function getBookingTypeAttribute()
    {
        return 'hall';
    }

    /**
     * Calculate duration in hours from start and end time.
     */
    public static function calculateDuration($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        return round($end->diffInMinutes($start) / 60, 2);
    }

    /**
     * Calculate total amount based on hall price and duration.
     */
    public static function calculateTotal($hallId, $duration)
    {
        $hall = Hall::findOrFail($hallId);
        return $hall->price_per_hour * $duration;
    }

    /**
     * Generate booking number: HB-YYYYMMDD-XXXX
     */
    public static function generateBookingNumber()
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return "HB-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if hall is available for given date and time.
     */
    public static function isAvailable($hallId, $eventDate, $startTime, $endTime, $excludeBookingId = null)
    {
        $hall = Hall::find($hallId);
        if (!$hall || $hall->status !== 'available') {
            return false;
        }

        $query = self::where('hall_id', $hallId)
            ->where('event_date', $eventDate)
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by hall.
     */
    public function scopeForHall($query, $hallId)
    {
        return $query->where('hall_id', $hallId);
    }
}
