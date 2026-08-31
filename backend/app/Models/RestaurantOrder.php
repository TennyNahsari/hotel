<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RestaurantOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'booking_id',
        'hall_booking_id',
        'customer_name',
        'total_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function hallBooking()
    {
        return $this->belongsTo(HallBooking::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems()
    {
        return $this->hasMany(RestaurantOrderItem::class);
    }

    // Boot method to auto-generate order number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'RO' . date('Ymd') . strtoupper(Str::random(6));
            }
        });
    }
}
