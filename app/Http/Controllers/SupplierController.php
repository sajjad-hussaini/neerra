<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierLedgerService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierLedgerService $ledgerService) {}

    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->latest()->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $ledgers = $supplier->ledgers()->paginate(20);
        return view('suppliers.show', compact('supplier', 'ledgers'));
    }

    public function makePayment(Request $request, Supplier $supplier)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'note' => 'nullable|string']);

        $this->ledgerService->recordPayment($supplier, $request->amount, $request->note);

        return back()->with('success', 'Payment recorded.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
