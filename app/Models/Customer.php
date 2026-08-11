<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Customer.php
class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'opening_balance'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function currentBalance(): float
    {
        return $this->ledgers()->latest('id')->value('balance_after') ?? $this->opening_balance;
    }
}
