<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Services\SupplierLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(protected SupplierLedgerService $supplierLedgerService) {}
    
    public function index()
    {
        $purchases = Purchase::withCount('items')->with('suppliers')->latest('purchased_at')->paginate(15);
        return view('purchases.index', compact('purchases'));
    }
    public function create()
    {
        return view('purchases.create', ['products' => Product::orderBy('name')->get(), 'suppliers' => $this->supplierLedgerService->getAllSuppliers()]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'          => ['nullable', 'exists:suppliers,id'],
            'purchased_at'         => ['required', 'date'],
            'paid_amount'          => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'    => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $total = collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['unit_cost']);
            $paid  = $data['paid_amount'] ?? 0;

            $purchase = Purchase::create([
                'reference_number' => 'PUR-' . now()->format('Ymd-His') . '-' . random_int(100, 999),
                'supplier_id'      => $data['supplier_id'] ?? null,
                'total'            => $total,
                'paid_amount'      => $paid,
                'due_amount'       => $total - $paid,
                'purchased_at'     => $data['purchased_at'],
            ]);

            foreach ($data['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'line_total' => $item['quantity'] * $item['unit_cost'],
                ]);

                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            if ($purchase->supplier_id) {
                app(SupplierLedgerService::class)->recordPurchase($purchase);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase saved and stock updated.');
    }
    public function show(Purchase $purchase)
    {
        $purchase->load('items.product', 'suppliers');
        return view('purchases.show', compact('purchase'));
    }
}
