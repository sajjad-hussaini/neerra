<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::withCount('items')->when($request->search, fn($q, $search) => $q->where('invoice_number', 'like', "%{$search}%"))->latest('sold_at')->paginate(15)->withQueryString();
        return view('orders.index', compact('sales'));
    }
    public function show(Sale $sale)
    {
        $sale->load('items.product');
        return view('orders.show', compact('sale'));
    }
}
