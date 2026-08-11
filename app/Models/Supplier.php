<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Supplier.php
class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'opening_balance'];

    protected $table = 'suppliers';

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function ledgers()
    {
        return $this->hasMany(SupplierLedger::class, 'supplier_id', 'id');
    }

    public function currentBalance(): float
    {
        return $this->ledgers()->latest('id')->value('balance_after') ?? $this->opening_balance;
    }
}
