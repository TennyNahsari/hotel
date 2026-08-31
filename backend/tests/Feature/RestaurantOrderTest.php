<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RestaurantOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_walk_in_restaurant_order_creation_and_standalone_payment()
    {
        $user = User::factory()->create();
        $menuItem = MenuItem::create([
            'name' => 'Nasi Goreng Spesial',
            'category' => 'food',
            'price' => 35000,
            'is_available' => true,
        ]);

        // 1. Create Walk-In Order
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/restaurant-orders', [
            'customer_name' => 'Pak Budi (Walk-In)',
            'items' => [
                [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => 2,
                ]
            ],
            'notes' => 'Tanpa cabai'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('customer_name', 'Pak Budi (Walk-In)')
            ->assertJsonPath('total_amount', '70000.00');

        $orderId = $response->json('id');
        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $orderId,
            'customer_name' => 'Pak Budi (Walk-In)',
            'booking_id' => null,
            'hall_booking_id' => null,
        ]);

        // 2. Update status to Delivered -> Should automatically create Standalone Payment
        $updateResponse = $this->actingAs($user, 'sanctum')->patchJson("/api/restaurant-orders/{$orderId}/status", [
            'status' => 'delivered'
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'delivered');

        // Assert payment record exists independently
        $this->assertDatabaseHas('payments', [
            'booking_id' => null,
            'hall_booking_id' => null,
            'restaurant_charges' => '70000.00',
        ]);

        $payment = Payment::where('restaurant_charges', 70000)->first();
        $this->assertNotNull($payment);
        $this->assertStringContainsString('Pak Budi (Walk-In)', $payment->notes);
    }
}
