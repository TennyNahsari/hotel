<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class Setting extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'hotel_branch_id',
        'key',
        'value',
    ];

    public static function get($key, $default = null, $branchId = null)
    {
        $query = static::where('key', $key);
        if ($branchId) {
            $query->where('hotel_branch_id', $branchId);
        } else {
            $query->whereNull('hotel_branch_id');
        }
        $setting = $query->first();
        if (!$setting) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $setting->value;
    }

    public static function set($key, $value, $branchId = null)
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value) : $value;
        return static::updateOrCreate(
            ['key' => $key, 'hotel_branch_id' => $branchId],
            ['value' => $encoded]
        );
    }
}
