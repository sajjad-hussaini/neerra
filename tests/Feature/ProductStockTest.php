<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    private function productData(mixed $stock): array
    {
        return [
            'name' => 'Fresh Milk', 'size' => '1 Litre', 'category' => 'Milk',
            'price' => 210, 'stock' => $stock, 'is_active' => 1,
        ];
    }

    public function test_existing_decimal_formatted_stock_can_be_saved_without_changes(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create($this->productData('265.00'));

        $this->get(route('products.edit', $product))->assertOk()->assertSee('step="0.01"', false);
        $this->put(route('products.update', $product), $this->productData('265.00'))
            ->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $this->assertEquals(265, $product->fresh()->stock);
    }

    public function test_fractional_stock_can_be_created_and_updated(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('products.store'), $this->productData('1.25'))
            ->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $product = Product::firstOrFail();
        $this->assertEquals(1.25, $product->stock);

        $this->put(route('products.update', $product), $this->productData('0.50'))
            ->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $this->assertEquals(0.5, $product->fresh()->stock);
    }

    public function test_invalid_stock_cannot_overwrite_existing_inventory(): void
    {
        $this->actingAs(User::factory()->create());
        $product = Product::create($this->productData(265));

        foreach ([-1, 'invalid', '1.001', '100000000', null] as $stock) {
            $this->putJson(route('products.update', $product), $this->productData($stock))
                ->assertUnprocessable()->assertJsonValidationErrors('stock');
            $this->assertEquals(265, $product->fresh()->stock);
        }
    }
}
