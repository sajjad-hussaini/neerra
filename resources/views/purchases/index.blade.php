@extends('backend.layout.layout')
@section('title', 'Purchase & Stock · Neerra')
@section('content')
<div class="page-head"><div><h1>Purchase & Stock</h1><p>Add purchases to increase product stock.</p></div><a class="primary" href="{{ route('purchases.create') }}">+ New purchase</a></div>
<div class="table-card"><table><thead><tr><th>Reference</th><th>Supplier</th><th>Purchase date</th><th>Items</th><th class="right">Total cost</th></tr></thead><tbody>@forelse($purchases as $purchase)<tr><td><strong>{{ $purchase->reference_number }}</strong></td><td>{{ $purchase->supplier ?: '—' }}</td><td>{{ $purchase->purchased_at->format('d M Y') }}</td><td>{{ $purchase->items_count }}</td><td class="right">Rs. {{ number_format($purchase->total) }}</td></tr>@empty<tr><td colspan="5" class="empty">No purchases added yet.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $purchases->links() }}</div>
@endsection
