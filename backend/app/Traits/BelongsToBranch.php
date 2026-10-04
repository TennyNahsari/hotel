<?php

namespace App\Traits;

use App\Models\HotelBranch;

trait BelongsToBranch
{
    /**
     * Relationship to HotelBranch
     */
    public function hotelBranch()
    {
        return $this->belongsTo(HotelBranch::class, 'hotel_branch_id');
    }

    /**
     * Local scope to filter by branch ID
     */
    public function scopeForBranch($query, $branchId)
    {
        if (empty($branchId)) {
            return $query;
        }

        return $query->where($this->getTable() . '.hotel_branch_id', $branchId);
    }
}
