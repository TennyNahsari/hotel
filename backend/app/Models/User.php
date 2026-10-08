<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'hotel_branch_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_global',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hotelBranch()
    {
        return $this->belongsTo(HotelBranch::class, 'hotel_branch_id');
    }

    public function hotelBranches()
    {
        return $this->belongsToMany(HotelBranch::class, 'user_hotel_branches');
    }

    public function getIsGlobalAttribute()
    {
        return is_null($this->hotel_branch_id) || $this->isSuperAdmin();
    }

    // Helper methods
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin') || $this->hasRole('admin');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    public function housekeepingTasks()
    {
        return $this->hasMany(HousekeepingTask::class, 'assigned_to');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
