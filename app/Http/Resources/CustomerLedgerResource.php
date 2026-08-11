<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,          // sale | payment | adjustment
            'sale_id'          => $this->sale_id,
            'debit'            => $this->debit,          // udhar charha
            'credit'           => $this->credit,         // wapas kiya
            'balance_after'    => $this->balance_after,
            'description'      => $this->description,
            'transaction_date' => $this->transaction_date,
        ];
    }
}