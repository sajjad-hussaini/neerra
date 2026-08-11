<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withSum('ledgers as total_udhar', 'debit')
            ->withSum('ledgers as total_paid', 'credit')
            ->orderBy('name')
            ->paginate(20);

        return view('customer.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $ledgers = $customer->ledgers()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate(20);

        $totalUdhar = $customer->ledgers()->sum('debit');  // total sale/udhaar diya (customer par charha)
        $totalPaid  = $customer->ledgers()->sum('credit'); // total wapis mila (payments)

        return view('customer.show', compact('customer', 'ledgers', 'totalUdhar', 'totalPaid'));
    }

    public function storeCredit(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($customer, $data) {
            $newBalance = $customer->currentBalance() + $data['amount'];

            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'type'             => 'sale',
                'description'      => $data['description'] ?? 'Udhar diya',
                'debit'            => $data['amount'],
                'credit'           => 0,
                'balance_after'    => $newBalance,
                'transaction_date' => now(),
            ]);
        });

        return back()->with('success', 'Udhar record ho gaya.');
    }

    public function storePayment(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($customer, $data) {
            $newBalance = $customer->currentBalance() - $data['amount'];

            CustomerLedger::create([
                'customer_id'      => $customer->id,
                'type'             => 'payment',
                'description'      => $data['note'] ?? 'Cash received',
                'debit'            => 0,
                'credit'           => $data['amount'],
                'balance_after'    => $newBalance,
                'transaction_date' => now(),
            ]);
        });

        return back()->with('success', 'Payment record ho gayi.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
        ]);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $customer = Customer::create($data);

        if ($request->expectsJson()) {
            return response()->json($customer->only('id', 'name', 'phone'), 201);
        }

        return back()->with('success', 'Customer add ho gaya.');
    }
}