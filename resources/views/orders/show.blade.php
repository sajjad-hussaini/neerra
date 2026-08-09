@extends('backend.layout.layout')
@section('title', 'Order ' . $sale->invoice_number)
@section('content')
    <div class="page-head">
        <div><a class="text-link" href="{{ route('orders.index') }}">← All orders</a>
            <h1>{{ $sale->invoice_number }}</h1>
            <p>{{ $sale->sold_at->format('d M Y, h:i A') }} · {{ $sale->payment_method }} payment ·
                {{ $sale->channel ?? 'POS' }}</p>
            @if ($sale->channel === 'Online')
                <p><strong>{{ $sale->customer_name }}</strong> ·
                    {{ $sale->customer_phone }}<br>{{ $sale->delivery_address }}</p>
            @endif
        </div>
        <span class="invoice-total">Rs. {{ number_format($sale->total) }}</span>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit price</th>
                    <th>Quantity</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td><strong>{{ $item->product?->name ?? 'Deleted product' }}</strong><small>{{ $item->product?->size }}</small>
                        </td>
                        <td>Rs. {{ number_format($item->unit_price) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="right">Rs. {{ number_format($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="right"><strong>Order total</strong></td>
                    <td class="right"><strong>Rs. {{ number_format($sale->total) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
