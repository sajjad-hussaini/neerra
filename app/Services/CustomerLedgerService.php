<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;

class CustomerLedgerService
{
    public function recordSale(Sale $sale): void
    {
        if (!$sale->customer_id) return; // walk-in cash customer, khata nahi

        $this->addEntry(
            customerId: $sale->customer_id,
            type: 'sale',
            saleId: $sale->id,
            debit: $sale->total,   // poora amount customer par charhta hai
            description: "Sale #{$sale->invoice_number}",
            date: $sale->sold_at,
        );

        if ($sale->paid_amount > 0) {
            $this->addEntry(
                customerId: $sale->customer_id,
                type: 'payment',
                saleId: $sale->id,
                credit: $sale->paid_amount,
                description: "Payment against #{$sale->invoice_number}",
                date: $sale->sold_at,
            );
        }
    }

    public function recordPayment(Customer $customer, float $amount, ?string $note = null): void
    {
        $this->addEntry(
            customerId: $customer->id,
            type: 'payment',
            credit: $amount,
            description: $note,
            date: now(),
        );
    }

    private function addEntry(
        int $customerId, string $type, float $debit = 0, float $credit = 0,
        ?int $saleId = null, ?string $description = null, $date = null,
    ): void {
        $last = CustomerLedger::where('customer_id', $customerId)->latest('id')->value('balance_after');
        $last ??= Customer::find($customerId)->opening_balance;

        CustomerLedger::create([
            'customer_id'      => $customerId,
            'type'             => $type,
            'sale_id'          => $saleId,
            'debit'            => $debit,
            'credit'           => $credit,
            'balance_after'    => $last + $debit - $credit,
            'description'      => $description,
            'transaction_date' => $date ?? now(),
        ]);
    }
}
