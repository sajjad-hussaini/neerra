<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/SupplierLedger.php
class SupplierLedger extends Model
{
    protected $fillable = [
        'supplier_id', 'type', 'purchase_id', 'debit', 'credit',
        'balance_after', 'description', 'transaction_date',
    ];

    protected $casts = ['transaction_date' => 'datetime'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
