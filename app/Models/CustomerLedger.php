<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/CustomerLedger.php — bilkul SupplierLedger jaisa, bas sale_id ke saath
class CustomerLedger extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'sale_id', 'debit', 'credit',
        'balance_after', 'description', 'transaction_date',
    ];

    protected $casts = ['transaction_date' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
