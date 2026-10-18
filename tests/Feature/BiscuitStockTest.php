<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiscuitStockTest extends TestCase
{
    use RefreshDatabase;

    private function biscuit(array $stocks = [10 => 50, 20 => 30, 30 => 0, 40 => 0, 50 => 10, 100 => 0]): Product
    {
        return Product::create([
            'name' => 'Biscuit', 'size' => '1 Piece', 'category' => 'Biscuits',
            'price' => 10, 'stock' => array_sum($stocks), 'price_stocks' => $stocks, 'is_active' => true,
        ]);
    }

    public function test_product_form_saves_separate_counts_and_calculates_total(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('products.create'))->assertOk()->assertSee('Biscuit stock by price');
        $data = [
            'name' => 'Biscuit', 'size' => '1 Piece', 'category' => 'Biscuits', 'price' => 10,
            'stock' => 999, 'is_active' => 1,
            'price_stocks' => [10 => 50, 20 => 30, 30 => 0, 40 => 0, 50 => 10, 100 => 0],
        ];
        $this->post(route('products.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $product = Product::firstOrFail();
        $this->assertEquals(90, $product->stock);
        $this->assertSame(30, $product->price_stocks[20]);
        $data['price_stocks'][20] = 25;
        $this->put(route('products.update', $product), $data)->assertSessionHasNoErrors();
        $this->assertEquals(85, $product->fresh()->stock);
        $this->get(route('products.index'))->assertOk()->assertSee('Rs. 20: 25 pcs');
    }

    public function test_invalid_price_counts_cannot_save_a_product(): void
    {
        $this->actingAs(User::factory()->create());
        $data = ['name' => 'Biscuit', 'size' => '1 Piece', 'category' => 'Biscuits', 'price' => 10,
            'price_stocks' => [10 => -1, 20 => 0.5, 30 => 0, 40 => 0, 50 => 0, 100 => 0]];
        $this->postJson(route('products.store'), $data)->assertUnprocessable()
            ->assertJsonValidationErrors(['price_stocks.10', 'price_stocks.20']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_duplicate_sale_lines_reserve_the_same_price_stock_together(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->biscuit([10 => 50, 20 => 1]);
        $line = ['product_id' => $product->id, 'unit_price' => 20, 'quantity' => 1, 'line_total' => 20];
        $this->postJson(route('sales.store'), ['payment_method' => 'Cash', 'items' => [$line, $line]])
            ->assertUnprocessable();
        $this->assertSame(1, $product->fresh()->price_stocks[20]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_purchase_restocks_only_the_selected_prices_and_records_them(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->biscuit();
        $this->post(route('purchases.store'), [
            'purchased_at' => '2026-10-18', 'paid_amount' => 0,
            'items' => [
                ['product_id' => $product->id, 'selling_price' => 20, 'quantity' => 5, 'unit_cost' => 15],
                ['product_id' => $product->id, 'selling_price' => 50, 'quantity' => 2, 'unit_cost' => 40],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('purchases.index'));
        $product->refresh();
        $this->assertSame(50, $product->price_stocks[10]);
        $this->assertSame(35, $product->price_stocks[20]);
        $this->assertSame(12, $product->price_stocks[50]);
        $this->assertEquals(97, $product->stock);
        $this->assertDatabaseHas('purchase_items', ['product_id' => $product->id, 'selling_price' => 20, 'quantity' => 5]);
    }

    public function test_purchase_requires_a_biscuit_price_and_rolls_back_without_it(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->biscuit();
        $this->postJson(route('purchases.store'), ['purchased_at' => '2026-10-18',
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => 5]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.selling_price');
        $this->assertEquals(90, $product->fresh()->stock);
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_existing_unallocated_stock_is_preserved_and_cannot_be_sold(): void
    {
        $this->actingAs(User::factory()->create());
        $product = $this->biscuit();
        $product->update(['price_stocks' => null, 'stock' => 100]);
        $this->get(route('products.edit', $product))->assertOk()->assertSee('Previous total: 100');
        $this->postJson(route('sales.store'), ['payment_method' => 'Cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10, 'line_total' => 10]],
        ])->assertUnprocessable();
        $this->assertEquals(100, $product->fresh()->stock);
        $this->assertNull($product->fresh()->price_stocks);
    }

    public function test_online_orders_respect_the_default_price_stock(): void
    {
        $product = $this->biscuit([10 => 1, 20 => 30]);
        $order = ['customer_name' => 'Test Customer', 'customer_phone' => '03001234567',
            'delivery_address' => 'Test address', 'payment_method' => 'Cash',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ];
        $this->postJson(route('shop.orders.store'), $order)->assertUnprocessable();
        $order['items'][0]['quantity'] = 1;
        $this->postJson(route('shop.orders.store'), $order)->assertCreated();
        $this->assertSame(0, $product->fresh()->price_stocks[10]);
        $this->assertSame(30, $product->fresh()->price_stocks[20]);
        $this->assertEquals(30, $product->fresh()->stock);
    }
}
