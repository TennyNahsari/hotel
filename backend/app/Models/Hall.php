<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hall_type',
        'floor',
        'capacity',
        'area_sqm',
        'price_per_hour',
        'facilities',
        'description',
        'image_url',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'area_sqm' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'facilities' => 'json',
    ];

    /**
     * Get the bookings for this hall.
     */
    public function bookings()
    {
        return $this->hasMany(HallBooking::class);
    }

    /**
     * Get the housekeeping tasks for this hall.
     */
    public function housekeepingTasks()
    {
        return $this->hasMany(HousekeepingTask::class);
    }

    /**
     * Scope to filter available halls.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to filter by hall type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('hall_type', $type);
    }
}
