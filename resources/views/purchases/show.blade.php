@extends('backend.layout.layout')

@section('title', 'Purchase Details · Neerra')

@section('content')

    <div class="page-head">
        <div>
            <h1>Purchase Details</h1>
            <p>{{ $purchase->reference_number }}</p>
        </div>

        <a class="button-light" href="{{ route('purchases.index') }}">
            ← Back to Purchases
        </a>
    </div>

    <div class="form-card">

        <div class="form-grid">

            <div>
                <label>Reference Number</label>
                <strong>{{ $purchase->reference_number }}</strong>
            </div>

            <div>
                <label>Supplier</label>
                <strong>
                    {{ $purchase->suppliers?->name ?? 'Cash Purchase' }}
                </strong>
            </div>

            <div>
                <label>Purchase Date</label>
                <strong>
                    {{ $purchase->purchased_at->format('d M Y') }}
                </strong>
            </div>

        </div>

        <hr>

        <h2>Purchase Items</h2>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th class="right">Quantity</th>
                        <th class="right">Unit Cost</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($purchase->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $item->product?->name ?? 'Product #' . $item->product_id }}
                            </td>

                            <td class="right">
                                {{ $item->quantity }}
                            </td>

                            <td class="right">
                                Rs. {{ number_format($item->unit_cost, 2) }}
                            </td>

                            <td class="right">
                                Rs. {{ number_format($item->quantity * $item->unit_cost, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">
                                No items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="purchase-summary">

            <div>
                <span>Total Cost</span>
                <strong>
                    Rs. {{ number_format($purchase->total, 2) }}
                </strong>
            </div>

            <div>
                <span>Paid Amount</span>
                <strong>
                    Rs. {{ number_format($purchase->paid_amount, 2) }}
                </strong>
            </div>

            <div>
                <span>Due Amount</span>
                <strong>
                    Rs. {{ number_format($purchase->due_amount, 2) }}
                </strong>
            </div>

        </div>

    </div>

@endsection