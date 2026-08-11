<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['reference_number', 'supplier', 'total', 'purchased_at'];
    protected function casts(): array
    {
        return ['purchased_at' => 'datetime'];
    }
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function ledgers()
    {
        return $this->hasMany(SupplierLedger::class);
    }
}
