<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        return view('welcome', ['products' => $products]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method' => ['required', 'in:Cash,Card,Online'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $total = 0; $lines = [];
            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                abort_if(! $product->is_active || $product->stock < $item['quantity'], 422, "{$product->name} ({$product->size}) does not have enough stock.");
                $lineTotal = $product->price * $item['quantity'];
                $total += $lineTotal; $lines[] = compact('product', 'item', 'lineTotal');
            }
            $sale = Sale::create(['invoice_number' => 'NRR-'.now()->format('Ymd-His').'-'.random_int(100, 999), 'payment_method' => $data['payment_method'], 'total' => $total, 'sold_at' => now()]);
            foreach ($lines as $line) {
                $sale->items()->create(['product_id' => $line['product']->id, 'quantity' => $line['item']['quantity'], 'unit_price' => $line['product']->price, 'line_total' => $line['lineTotal']]);
                $line['product']->decrement('stock', $line['item']['quantity']);
            }

            $this->customerLedgerService->recordSale($sale);
            
            return $sale;
        });
        return response()->json(['message' => 'Sale saved successfully.', 'invoice_number' => $sale->invoice_number, 'total' => $sale->total], 201);
    }

    public function stats()
    {
        $base = Sale::query();
        return response()->json(['today_total' => (clone $base)->whereDate('sold_at', today())->sum('total'), 'today_orders' => (clone $base)->whereDate('sold_at', today())->count(), 'month_total' => (clone $base)->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'), 'year_total' => (clone $base)->whereYear('sold_at', now()->year)->sum('total')]);
    }
}
