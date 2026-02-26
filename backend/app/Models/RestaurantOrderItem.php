<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_order_id',
        'menu_item_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function restaurantOrder()
    {
        return $this->belongsTo(RestaurantOrder::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
