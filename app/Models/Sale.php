<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['invoice_number', 'payment_method', 'channel', 'customer_name', 'customer_phone', 'delivery_address', 'status', 'total', 'sold_at'];
    protected function casts(): array { return ['sold_at' => 'datetime']; }
    public function items() { return $this->hasMany(SaleItem::class); }
}
