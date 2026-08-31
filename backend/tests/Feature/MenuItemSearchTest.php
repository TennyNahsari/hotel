<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\MenuItem;
use App\Models\User;

class MenuItemSearchTest extends TestCase
{
    public function test_menu_item_search_case_insensitive_and_empty_is_available()
    {
        $user = User::factory()->create();

        $item1 = MenuItem::create([
            'name' => 'Nasi Goreng Spesial',
            'category' => 'food',
            'price' => 25000,
            'description' => 'Lezat dan gurih',
            'is_available' => true,
        ]);

        $item2 = MenuItem::create([
            'name' => 'Es Teh Manis',
            'category' => 'beverage',
            'price' => 5000,
            'description' => 'Segar dingin',
            'is_available' => true,
        ]);

        // Test lowercase search for 'nasi'
        $response = $this->actingAs($user)->getJson('/api/menu-items?search=nasi');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $names = array_column($data, 'name');
        $this->assertContains('Nasi Goreng Spesial', $names);

        // Test empty is_available param (should not filter out available items)
        $response2 = $this->actingAs($user)->getJson('/api/menu-items?is_available=');
        $response2->assertStatus(200);
        $data2 = $response2->json('data');
        $this->assertNotEmpty($data2);

        // Clean up
        $item1->delete();
        $item2->delete();
        $user->delete();
    }
}
