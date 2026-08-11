<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierLedgerResource;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierLedgerService;
use Illuminate\Http\Request;

// app/Http/Controllers/SupplierController.php
class SupplierController extends Controller
{
    public function __construct(protected SupplierLedgerService $ledgerService) {}

    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->get();
        return SupplierResource::collection($suppliers);
    }

    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());
        return new SupplierResource($supplier);
    }

    public function ledger(Supplier $supplier)
    {
        $entries = $supplier->ledgers()->latest('transaction_date')->paginate(20);
        return SupplierLedgerResource::collection($entries)
            ->additional(['meta' => ['current_balance' => $supplier->currentBalance()]]);
    }

    public function makePayment(Request $request, Supplier $supplier)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'note' => 'nullable|string']);
        $this->ledgerService->recordPayment($supplier, $request->amount, $request->note);
        return response()->json(['balance' => $supplier->currentBalance()]);
    }
}
