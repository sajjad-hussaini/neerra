<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'size', 'category', 'price', 'stock', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function saleItems() { return $this->hasMany(SaleItem::class); }
}
