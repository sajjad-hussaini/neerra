<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index() { $purchases = Purchase::withCount('items')->latest('purchased_at')->paginate(15); return view('purchases.index', compact('purchases')); }
    public function create() { return view('purchases.create', ['products' => Product::orderBy('name')->get()]); }
    public function store(Request $request)
    {
        $data = $request->validate(['supplier' => ['nullable', 'string', 'max:100'], 'purchased_at' => ['required', 'date'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'exists:products,id', 'distinct'], 'items.*.quantity' => ['required', 'integer', 'min:1'], 'items.*.unit_cost' => ['required', 'integer', 'min:0']]);
        DB::transaction(function () use ($data) {
            $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_cost']);
            $purchase = Purchase::create(['reference_number' => 'PUR-'.now()->format('Ymd-His').'-'.random_int(100, 999), 'supplier' => $data['supplier'], 'total' => $total, 'purchased_at' => $data['purchased_at']]);
            foreach ($data['items'] as $item) { $product = Product::lockForUpdate()->findOrFail($item['product_id']); $lineTotal = $item['quantity'] * $item['unit_cost']; $purchase->items()->create(['product_id' => $product->id, 'quantity' => $item['quantity'], 'unit_cost' => $item['unit_cost'], 'line_total' => $lineTotal]); $product->increment('stock', $item['quantity']); }
        });
        return redirect()->route('purchases.index')->with('success', 'Purchase saved and stock updated successfully.');
    }
}
