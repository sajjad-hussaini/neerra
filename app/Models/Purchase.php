<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['reference_number', 'supplier_id', 'total', 'purchased_at', 'paid_amount', 'due_amount'];

    protected $table = 'purchases';

    protected function casts(): array
    {
        return ['purchased_at' => 'datetime'];
    }
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
    
    public function ledgers()
    {
        return $this->hasMany(SupplierLedger::class);
    }
}
