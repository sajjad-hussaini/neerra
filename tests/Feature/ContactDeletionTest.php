<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_deleted_while_sales_and_khata_history_remain(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::create(['name' => 'Old Customer', 'opening_balance' => 0]);
        $sale = Sale::create([
            'invoice_number' => 'TEST-CUSTOMER', 'customer_id' => $customer->id,
            'payment_method' => 'Cash', 'total' => 100, 'paid_amount' => 0,
            'due_amount' => 100, 'sold_at' => now(),
        ]);
        CustomerLedger::create([
            'customer_id' => $customer->id, 'sale_id' => $sale->id,
            'type' => 'sale', 'debit' => 100, 'credit' => 0,
            'balance_after' => 100, 'transaction_date' => now(),
        ]);

        $this->get(route('customers.index'))->assertOk()->assertSee(route('customers.destroy', $customer));
        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))->assertSessionHas('success');

        $this->assertNull(Customer::find($customer->id));
        $this->assertNotNull(Customer::withTrashed()->find($customer->id));
        $this->assertEquals($customer->id, $sale->fresh()->customer_id);
        $this->assertEquals(100, $customer->ledgers()->firstOrFail()->balance_after);
        $this->assertSame('Old Customer', $sale->fresh()->customer->name);
        $this->get(route('customers.index'))->assertDontSee('Old Customer');
        $this->get(route('pos'))->assertDontSee('Old Customer');
        $this->get(route('orders.index'))->assertSee('Old Customer (deleted)');
        $this->delete(route('customers.destroy', $customer))->assertNotFound();
    }

    public function test_supplier_can_be_deleted_while_purchases_and_khata_history_remain(): void
    {
        $this->actingAs(User::factory()->create());
        $supplier = Supplier::create(['name' => 'Old Supplier', 'opening_balance' => 0]);
        $purchase = Purchase::create([
            'reference_number' => 'TEST-SUPPLIER', 'supplier_id' => $supplier->id,
            'total' => 100, 'paid_amount' => 0, 'due_amount' => 100,
            'purchased_at' => now(),
        ]);
        SupplierLedger::create([
            'supplier_id' => $supplier->id, 'purchase_id' => $purchase->id,
            'type' => 'purchase', 'debit' => 0, 'credit' => 100,
            'balance_after' => 100, 'transaction_date' => now(),
        ]);

        $this->get(route('suppliers.index'))->assertOk()->assertSee(route('suppliers.destroy', $supplier));
        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))->assertSessionHas('success');

        $this->assertNull(Supplier::find($supplier->id));
        $this->assertNotNull(Supplier::withTrashed()->find($supplier->id));
        $this->assertEquals($supplier->id, $purchase->fresh()->supplier_id);
        $this->assertEquals(100, $supplier->ledgers()->firstOrFail()->balance_after);
        $this->assertSame('Old Supplier', $purchase->fresh()->suppliers->name);
        $this->get(route('suppliers.index'))->assertDontSee('Old Supplier');
        $this->get(route('purchases.create'))->assertDontSee('Old Supplier');
        $this->get(route('purchases.index'))->assertSee('Old Supplier');
        $this->delete(route('suppliers.destroy', $supplier))->assertNotFound();
    }
}
