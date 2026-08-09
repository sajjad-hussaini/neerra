@extends('backend.layout.layout')
@section('title', 'Orders · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Orders</h1>
            <p>POS and online customer orders, newest first.</p>
        </div><a class="primary" href="{{ route('pos') }}">New sale</a>
    </div>
    <form class="filter" method="get"><input name="search" value="{{ request('search') }}"
            placeholder="Search invoice number"><button>Search</button></form>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Channel</th>
                    <th>Customer</th>
                    <th>Date & time</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td><strong>{{ $sale->invoice_number }}</strong><small>{{ $sale->payment_method }}</small></td>
                        <td><span class="status info">{{ $sale->channel ?? 'POS' }}</span></td>
                        <td>{{ $sale->customer_name ?: 'Walk-in customer' }}</td>
                        <td>{{ $sale->sold_at->format('d M Y, h:i A') }}</td>
                        <td><span
                                class="status {{ ($sale->status ?? 'Completed') === 'Pending' ? 'muted' : 'success' }}">{{ $sale->status ?? 'Completed' }}</span>
                        </td>
                        <td><strong>Rs. {{ number_format($sale->total) }}</strong></td>
                        <td><a class="text-link" href="{{ route('orders.show', $sale) }}">View →</a></td>
                </tr>@empty<tr>
                        <td colspan="7" class="empty">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $sales->links() }}</div>
@endsection
