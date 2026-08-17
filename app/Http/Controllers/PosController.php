<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(protected CustomerLedgerService $customerLedgerService) {}

    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone']);
        return view('welcome', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:Cash,Card,Online'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'min:0', 'max:100'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.line_total' => ['required', 'integer', 'min:0'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $total = 0; $lines = [];
            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                abort_if(! $product->is_active || $product->stock < $item['quantity'], 422, "{$product->name} ({$product->size}) does not have enough stock.");
                $lineTotal = $item['line_total'];
                $total += $lineTotal; $lines[] = compact('product', 'item', 'lineTotal');
            }

            $customerId = $data['customer_id'] ?? null;
            $paid = array_key_exists('paid_amount', $data) && $data['paid_amount'] !== null
                ? (int) round($data['paid_amount'])
                : $total;
            $paid = max(0, min($paid, $total));
            $due = $total - $paid;
            abort_if($due > 0 && ! $customerId, 422, 'Select a customer for Udhar.');

            $sale = Sale::create([
                'invoice_number' => 'NRR-'.now()->format('Ymd-His').'-'.random_int(100, 999),
                'customer_id' => $customerId,
                'payment_method' => $data['payment_method'],
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $due,
                'sold_at' => now(),
            ]);
            foreach ($lines as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['item']['quantity'],
                    'unit_price' => $line['item']['unit_price'],
                    'line_total' => $line['lineTotal']
                ]);
                $line['product']->decrement('stock', $line['item']['quantity']);
            }

            $this->customerLedgerService->recordSale($sale);

            return $sale;
        });
        return response()->json(['message' => 'Sale saved successfully.', 'invoice_number' => $sale->invoice_number, 'total' => $sale->total, 'paid' => $sale->paid_amount, 'due' => $sale->due_amount], 201);
    }

    public function stats()
    {
        $base = Sale::query();
        return response()->json(['today_total' => (clone $base)->whereDate('sold_at', today())->sum('total'), 'today_orders' => (clone $base)->whereDate('sold_at', today())->count(), 'month_total' => (clone $base)->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'), 'year_total' => (clone $base)->whereYear('sold_at', now()->year)->sum('total')]);
    }
}
