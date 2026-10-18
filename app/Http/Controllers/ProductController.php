<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request) { $products = Product::when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%"))->latest()->paginate(12)->withQueryString(); return view('products.index', compact('products')); }
    public function create() { return view('products.form', ['product' => new Product]); }
    public function store(Request $request) { Product::create($this->validated($request)); return redirect()->route('products.index')->with('success', 'Product added successfully.'); }
    public function edit(Product $product) { return view('products.form', compact('product')); }
    public function update(Request $request, Product $product) { $product->update($this->validated($request, $product)); return redirect()->route('products.index')->with('success', 'Product updated successfully.'); }
    public function destroy(Product $product) { if ($product->saleItems()->exists()) { $product->update(['is_active' => false]); return redirect()->route('products.index')->with('success', 'Product has sales history, so it was archived instead.'); } $product->delete(); return redirect()->route('products.index')->with('success', 'Product removed successfully.'); }
    private function validated(Request $request, ?Product $product = null): array
    {
        $isBiscuit = (new Product($request->only('name', 'category')))->isBiscuit();
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'size' => ['required', 'string', 'max:50', Rule::unique('products')->where(fn ($q) => $q->where('name', $request->name))->ignore($product)],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'integer', 'min:1'],
            'stock' => [$isBiscuit ? 'nullable' : 'required', 'integer', 'min:0', 'max:99999999'],
            'is_active' => ['nullable', 'boolean'],
        ];
        if ($isBiscuit) {
            $rules['price_stocks'] = ['required', 'array:10,20,30,40,50,100'];
            foreach (Product::BISCUIT_PRICES as $price) {
                $rules["price_stocks.$price"] = ['required', 'integer', 'min:0', 'max:16666666'];
            }
        }
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');
        $data['price_stocks'] = $isBiscuit ? array_map('intval', $data['price_stocks']) : null;
        if ($isBiscuit) $data['stock'] = array_sum($data['price_stocks']);
        return $data;
    }
}
