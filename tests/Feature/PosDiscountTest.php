<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosDiscountTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->product = Product::create([
            'name' => 'Water', 'size' => '1L', 'category' => 'Water',
            'price' => 200, 'stock' => 5, 'is_active' => true,
        ]);
    }

    private function sale(array $overrides = []): array
    {
        return array_replace([
            'payment_method' => 'Cash',
            'discount_enabled' => true,
            'discount_amount' => 50,
            'items' => [[
                'product_id' => $this->product->id, 'quantity' => 1,
                'unit_price' => 200, 'line_total' => 200,
            ]],
        ], $overrides);
    }

    public function test_discount_is_saved_and_stock_uses_the_original_quantity(): void
    {
        $this->postJson(route('sales.store'), $this->sale())
            ->assertCreated()->assertJsonPath('subtotal', 200)
            ->assertJsonPath('discount_amount', 50)->assertJsonPath('total', 150)
            ->assertJsonPath('paid', 150)->assertJsonPath('due', 0);

        $this->assertDatabaseHas('sales', ['discount_amount' => 50, 'total' => 150]);
        $this->assertDatabaseHas('sale_items', ['quantity' => 1, 'line_total' => 200]);
        $this->assertEquals(4, $this->product->fresh()->stock);
        $this->get(route('orders.show', Sale::first()))->assertOk()->assertSee('Discount')->assertSee('Subtotal');
        $this->getJson(route('sales.stats'))->assertOk()->assertJsonPath('today_total', 150);
    }

    public function test_disabled_or_omitted_checkbox_leaves_the_total_undiscounted(): void
    {
        foreach ([false, null] as $enabled) {
            $payload = $this->sale(['discount_enabled' => $enabled]);
            if ($enabled === null) {
                unset($payload['discount_enabled'], $payload['discount_amount']);
            }
            $this->postJson(route('sales.store'), $payload)->assertCreated()
                ->assertJsonPath('discount_amount', 0)->assertJsonPath('total', 200);
        }
    }

    public function test_invalid_discounts_do_not_create_sales_or_deduct_stock(): void
    {
        foreach ([-1, 201, 0.5, 'invalid', null] as $amount) {
            $this->postJson(route('sales.store'), $this->sale(['discount_amount' => $amount]))
                ->assertUnprocessable()->assertJsonValidationErrors('discount_amount');
        }
        $payload = $this->sale();
        unset($payload['discount_amount']);
        $this->postJson(route('sales.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('discount_amount');
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertEquals(5, $this->product->fresh()->stock);
    }

    public function test_customer_ledger_and_due_use_the_discounted_total(): void
    {
        $customer = Customer::create(['name' => 'Test Customer', 'opening_balance' => 0]);
        $this->postJson(route('sales.store'), $this->sale([
            'customer_id' => $customer->id, 'paid_amount' => 100,
        ]))->assertCreated()->assertJsonPath('total', 150)->assertJsonPath('paid', 100)->assertJsonPath('due', 50);

        $this->assertEquals(50, $customer->currentBalance());
        $this->assertDatabaseHas('customer_ledgers', ['customer_id' => $customer->id, 'type' => 'sale', 'debit' => 150]);
    }

    public function test_full_discount_needs_no_payment_or_customer(): void
    {
        $this->postJson(route('sales.store'), $this->sale(['discount_amount' => 200, 'paid_amount' => 200]))
            ->assertCreated()->assertJsonPath('total', 0)->assertJsonPath('paid', 0)->assertJsonPath('due', 0);
        $this->assertEquals(4, $this->product->fresh()->stock);
    }
}
