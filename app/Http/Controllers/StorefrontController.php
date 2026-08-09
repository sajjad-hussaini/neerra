<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function index()
    {
        return view('storefront.index', ['products' => Product::where('is_active', true)->where('stock', '>', 0)->orderBy('category')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'], 'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:1000'], 'payment_method' => ['required', 'in:Cash,Online'],
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $sale = DB::transaction(function () use ($data) {
            $total = 0; $lines = [];
            foreach ($data['items'] as $item) { $product = Product::lockForUpdate()->findOrFail($item['product_id']); abort_if(! $product->is_active || $product->stock < $item['quantity'], 422, "{$product->name} is currently out of stock."); $lineTotal = $product->price * $item['quantity']; $total += $lineTotal; $lines[] = compact('product', 'item', 'lineTotal'); }
            $sale = Sale::create(['invoice_number' => 'ONL-'.now()->format('Ymd-His').'-'.random_int(100, 999), 'payment_method' => $data['payment_method'], 'channel' => 'Online', 'customer_name' => $data['customer_name'], 'customer_phone' => $data['customer_phone'], 'delivery_address' => $data['delivery_address'], 'status' => 'Pending', 'total' => $total, 'sold_at' => now()]);
            foreach ($lines as $line) { $sale->items()->create(['product_id' => $line['product']->id, 'quantity' => $line['item']['quantity'], 'unit_price' => $line['product']->price, 'line_total' => $line['lineTotal']]); $line['product']->decrement('stock', $line['item']['quantity']); }
            return $sale;
        });
        return response()->json(['message' => 'Your order has been placed.', 'order_number' => $sale->invoice_number], 201);
    }
}
