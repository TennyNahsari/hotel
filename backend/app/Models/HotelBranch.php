<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'city',
        'phone',
        'email',
        'description',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function halls()
    {
        return $this->hasMany(Hall::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function hallBookings()
    {
        return $this->hasMany(HallBooking::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hotel_branches');
    }
}
