<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerLedgerResource;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;

// app/Http/Controllers/CustomerController.php — same shape
class CustomerController extends Controller
{
    public function __construct(protected CustomerLedgerService $ledgerService) {}

    public function index()
    {
        return CustomerResource::collection(Customer::withCount('sales')->get());
    }

    public function store(CustomerRequest $request)
    {
        return new CustomerResource(Customer::create($request->validated()));
    }

    public function ledger(Customer $customer)
    {
        $entries = $customer->ledgers()->latest('transaction_date')->paginate(20);
        return CustomerLedgerResource::collection($entries)
            ->additional(['meta' => ['current_balance' => $customer->currentBalance()]]);
    }

    public function makePayment(Request $request, Customer $customer)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'note' => 'nullable|string']);
        $this->ledgerService->recordPayment($customer, $request->amount, $request->note);
        return response()->json(['balance' => $customer->currentBalance()]);
    }
}
