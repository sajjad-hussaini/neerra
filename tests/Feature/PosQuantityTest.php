<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_saves_fractional_quantity_and_deducts_only_that_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create([
            'name' => 'Water', 'size' => '1L', 'category' => 'water',
            'price' => 200, 'stock' => 1.5, 'is_active' => true,
        ]);

        $this->postJson(route('sales.store'), $this->sale($product, 0.5))
            ->assertCreated()->assertJsonPath('total', 100);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id, 'quantity' => 0.5,
            'unit_price' => 200, 'line_total' => 100,
        ]);
        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_invalid_quantities_are_rejected_without_changing_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create([
            'name' => 'Water', 'size' => '1L', 'category' => 'water',
            'price' => 200, 'stock' => 1.5, 'is_active' => true,
        ]);

        foreach ([0, -1, 'invalid', 0.001] as $quantity) {
            $this->postJson(route('sales.store'), $this->sale($product, $quantity))
                ->assertUnprocessable()->assertJsonValidationErrors('items.0.quantity');
        }
        $this->assertEquals(1.5, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    private function sale(Product $product, mixed $quantity): array
    {
        return [
            'payment_method' => 'Cash',
            'items' => [[
                'product_id' => $product->id, 'quantity' => $quantity,
                'unit_price' => 200, 'line_total' => 100,
            ]],
        ];
    }
}
