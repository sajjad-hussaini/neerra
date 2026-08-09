@extends('backend.layout.layout')
@section('title', 'Orders · Neerra')
@section('content')
<div class="page-head"><div><h1>Orders</h1><p>Every completed sale, newest first.</p></div><a class="primary" href="{{ route('pos') }}">New sale</a></div><form class="filter" method="get"><input name="search" value="{{ request('search') }}" placeholder="Search invoice number"><button>Search</button></form>
<div class="table-card"><table><thead><tr><th>Invoice</th><th>Date & time</th><th>Payment</th><th>Items</th><th>Total</th><th></th></tr></thead><tbody>@forelse($sales as $sale)<tr><td><strong>{{ $sale->invoice_number }}</strong></td><td>{{ $sale->sold_at->format('d M Y, h:i A') }}</td><td><span class="status info">{{ $sale->payment_method }}</span></td><td>{{ $sale->items_count }}</td><td><strong>Rs. {{ number_format($sale->total) }}</strong></td><td><a class="text-link" href="{{ route('orders.show',$sale) }}">View →</a></td></tr>@empty<tr><td colspan="6" class="empty">No completed orders yet.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $sales->links() }}</div>
@endsection
