<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $sales = Sale::whereBetween('sold_at', [$from, $to]);
        $summary = ['revenue' => (clone $sales)->sum('total'), 'orders' => (clone $sales)->count(), 'average' => (clone $sales)->avg('total') ?? 0];
        $daily = (clone $sales)->selectRaw('DATE(sold_at) as day, SUM(total) as total, COUNT(*) as orders')->groupBy('day')->orderBy('day')->get();
        $topProducts = SaleItem::query()->select('products.name', 'products.size', DB::raw('SUM(sale_items.quantity) as units'), DB::raw('SUM(sale_items.line_total) as revenue'))->join('sales', 'sales.id', '=', 'sale_items.sale_id')->join('products', 'products.id', '=', 'sale_items.product_id')->whereBetween('sales.sold_at', [$from, $to])->groupBy('products.id', 'products.name', 'products.size')->orderByDesc('units')->limit(8)->get();
        return view('reports.sales', compact('from', 'to', 'summary', 'daily', 'topProducts'));
    }
}
