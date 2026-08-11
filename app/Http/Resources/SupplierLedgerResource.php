<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,          // purchase | payment | adjustment
            'purchase_id'      => $this->purchase_id,
            'debit'            => $this->debit,          // aapne payment ki
            'credit'           => $this->credit,         // aap par charha (maal aya)
            'balance_after'    => $this->balance_after,
            'description'      => $this->description,
            'transaction_date' => $this->transaction_date,
        ];
    }
}