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

    public function test_one_biscuit_product_deducts_only_the_stock_of_the_selected_prices(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create([
            'name' => 'Biscuits', 'size' => '1 Piece', 'category' => 'Biscuits',
            'price' => 200, 'stock' => 5, 'is_active' => true, 'price_stocks' => [20 => 2, 50 => 3],
        ]);
        $this->postJson(route('sales.store'), [
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 20, 'line_total' => 40],
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50, 'line_total' => 50],
            ],
        ])->assertCreated()->assertJsonPath('total', 90);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('sale_items', 2);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 20, 'line_total' => 40,
        ]);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50, 'line_total' => 50,
        ]);
        $this->assertEquals(2, $product->fresh()->stock);
        $this->assertSame(0, $product->fresh()->price_stocks[20]);
        $this->assertSame(2, $product->fresh()->price_stocks[50]);
    }

    public function test_different_biscuit_prices_cannot_oversell_the_same_product(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create([
            'name' => 'Biscuits', 'size' => '1 Piece', 'category' => 'Biscuits',
            'price' => 200, 'stock' => 2, 'is_active' => true, 'price_stocks' => [20 => 1, 50 => 1],
        ]);
        $this->postJson(route('sales.store'), [
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 20, 'line_total' => 40],
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50, 'line_total' => 50],
            ],
        ])->assertUnprocessable();

        $this->assertEquals(2, $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    public function test_biscuits_require_a_supported_unit_price_and_whole_quantity(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create([
            'name' => 'Biscuits', 'size' => '1 Piece', 'category' => 'Biscuits',
            'price' => 200, 'stock' => 5, 'is_active' => true, 'price_stocks' => [20 => 2, 50 => 3],
        ]);
        foreach ([[0.5, 20], [1, 25]] as [$qty, $price]) {
            $this->postJson(route('sales.store'), [
                'payment_method' => 'Cash',
                'items' => [[
                    'product_id' => $product->id, 'quantity' => $qty,
                    'unit_price' => $price, 'line_total' => 10,
                ]],
            ])->assertUnprocessable();
        }
        $this->assertEquals(5, $product->fresh()->stock);
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
