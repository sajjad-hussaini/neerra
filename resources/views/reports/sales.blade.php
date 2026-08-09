@extends('backend.layout.layout')
@section('title', 'Sales Report · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Sales Report</h1>
            <p>Revenue and products sold for your selected period.</p>
        </div>
    </div>
    <form class="date-filter" method="get"><label>From<input type="date" name="from"
                value="{{ $from->toDateString() }}"></label><label>To<input type="date" name="to"
                value="{{ $to->toDateString() }}"></label><button class="primary">Apply report</button></form>
    <section class="stats report-stats">
        <div class="stat"><small>REVENUE</small><strong>Rs. {{ number_format($summary['revenue']) }}</strong></div>
        <div class="stat"><small>ORDERS</small><strong>{{ number_format($summary['orders']) }}</strong></div>
        <div class="stat"><small>AVERAGE ORDER</small><strong>Rs. {{ number_format($summary['average']) }}</strong></div>
    </section>
    <div class="report-grid">
        <section class="table-card">
            <div class="card-title">
                <h2>Daily sales</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Orders</th>
                        <th class="right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daily as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                            <td>{{ $row->orders }}</td>
                            <td class="right">Rs. {{ number_format($row->total) }}</td>
                    </tr>@empty<tr>
                            <td colspan="3" class="empty">No sales in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="table-card">
            <div class="card-title">
                <h2>Top products</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Units</th>
                        <th class="right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong><small>{{ $item->size }}</small></td>
                            <td>{{ $item->units }}</td>
                            <td class="right">Rs. {{ number_format($item->revenue) }}</td>
                    </tr>@empty<tr>
                            <td colspan="3" class="empty">No products sold yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
