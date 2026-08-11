<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'phone'            => $this->phone,
            'address'          => $this->address,
            'opening_balance'  => $this->opening_balance,
            'current_balance'  => $this->currentBalance(),
            'purchases_count'  => $this->whenCounted('purchases'),
            'created_at'       => $this->created_at,
        ];
    }
}