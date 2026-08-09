<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@neerra.com'], ['name' => 'Neerra Admin', 'password' => Hash::make('Neerra@123')]);
        Product::upsert([
            ['name' => 'Fresh Milk', 'size' => '1.9 Litre', 'category' => 'Milk', 'price' => 390, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fresh Milk', 'size' => '1 Litre', 'category' => 'Milk', 'price' => 210, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fresh Milk', 'size' => 'Half Litre', 'category' => 'Milk', 'price' => 110, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mineral Water', 'size' => '1.9 Litre', 'category' => 'Water', 'price' => 90, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mineral Water', 'size' => '1 Litre', 'category' => 'Water', 'price' => 55, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mineral Water', 'size' => 'Half Litre', 'category' => 'Water', 'price' => 35, 'stock' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['name', 'size'], ['price', 'stock', 'is_active', 'updated_at']);
    }
}
