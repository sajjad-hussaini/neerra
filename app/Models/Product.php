<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'size', 'category', 'price', 'stock', 'is_active', 'price_stocks'];
    protected function casts(): array { return ['is_active' => 'boolean', 'price_stocks' => 'array']; }
    public const BISCUIT_PRICES = [10, 20, 30, 40, 50, 100];

    public function isBiscuit(): bool
    {
        return (bool) preg_match('/biscuit|biscut|cookie/i', $this->category.' '.$this->name);
    }

    public function stockForPrice(int $price): float
    {
        return $this->isBiscuit() ? (float) ($this->price_stocks[$price] ?? 0) : (float) $this->stock;
    }

    // Call while holding the product row lock inside a transaction.
    public function changeStock(float $quantity, int $price): void
    {
        if ($this->isBiscuit()) {
            $stocks = $this->price_stocks ?? [];
            $stocks[$price] = (int) ($stocks[$price] ?? 0) + (int) $quantity;
            $this->price_stocks = $stocks;
            $this->stock = array_sum($stocks);
            $this->save();
        } else {
            $this->increment('stock', $quantity);
        }
    }

    public function saleItems() { return $this->hasMany(SaleItem::class); }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }
}
