<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierLedger;

// app/Services/SupplierLedgerService.php
class SupplierLedgerService
{
    public function recordPurchase(Purchase $purchase): void
    {
        if (!$purchase->supplier_id) return; // cash purchase, khata nahi banega

        $due = $purchase->total - $purchase->paid_amount;

        $this->addEntry(
            supplierId: $purchase->supplier_id,
            type: 'purchase',
            purchaseId: $purchase->id,
            credit: $purchase->total,   // poora maal charhta hai
            description: "Purchase #{$purchase->reference_number}",
            date: $purchase->purchased_at,
        );

        // agar usi waqt kuch payment ki gayi ho (partial ya full)
        if ($purchase->paid_amount > 0) {
            $this->addEntry(
                supplierId: $purchase->supplier_id,
                type: 'payment',
                purchaseId: $purchase->id,
                debit: $purchase->paid_amount,
                description: "Payment against #{$purchase->reference_number}",
                date: $purchase->purchased_at,
            );
        }
    }

    public function recordPayment(Supplier $supplier, float $amount, ?string $note = null): void
    {
        $this->addEntry(
            supplierId: $supplier->id,
            type: 'payment',
            debit: $amount,
            description: $note,
            date: now(),
        );
    }

    private function addEntry(
        int $supplierId, string $type, float $debit = 0, float $credit = 0,
        ?int $purchaseId = null, ?string $description = null, $date = null,
    ): void {
        $last = SupplierLedger::where('supplier_id', $supplierId)->latest('id')->value('balance_after');
        $last ??= Supplier::find($supplierId)->opening_balance;

        SupplierLedger::create([
            'supplier_id'      => $supplierId,
            'type'             => $type,
            'purchase_id'      => $purchaseId,
            'debit'            => $debit,
            'credit'           => $credit,
            'balance_after'    => $last + $credit - $debit,
            'description'      => $description,
            'transaction_date' => $date ?? now(),
        ]);
    }
}
