@extends('backend.layout.layout')
@section('title', $supplier->name . ' · Neerra')
@section('content')
  <div class="page-head">
    <div>
        <h1>{{ $supplier->name }}</h1>
        <p>{{ $supplier->phone ?: '—' }}</p>
    </div>
    <a class="button-light" href="{{ route('suppliers.index') }}">Back</a>
</div>

<div class="stats-row">
    <div class="stat-box">
        <span class="label">Total Purchased</span>
        <strong>Rs. {{ number_format($ledgers->sum('credit')) }}</strong>
    </div>
    <div class="stat-box">
        <span class="label">Total Paid</span>
        <strong>Rs. {{ number_format($ledgers->sum('debit')) }}</strong>
    </div>
    <div class="stat-box highlight">
        <span class="label">Outstanding Balance</span>
        <strong>Rs. {{ number_format($supplier->currentBalance()) }}</strong>
    </div>
</div>

    <form class="form-card" method="post" action="{{ route('suppliers.payment', $supplier) }}">@csrf
        <div class="form-grid">
            <label>Payment amount<input type="number" step="0.01" name="amount" required></label>
            <label>Note<input name="note" placeholder="e.g. Cash paid"></label>
        </div>
        <button class="primary">Record payment</button>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="right">Credit (aap kyy traf)</th>
                    <th class="right">Debit (aapne diya)</th>
                    <th class="right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgers as $entry)
                    <tr>
                        <td>{{ $entry->transaction_date->format('d M Y') }}</td>
                        <td>{{ ucfirst($entry->type) }}</td>
                        <td>{{ $entry->description ?: '—' }}</td>
                        <td class="right">{{ $entry->credit ? 'Rs. ' . number_format($entry->credit) : '—' }}</td>
                        <td class="right">{{ $entry->debit ? 'Rs. ' . number_format($entry->debit) : '—' }}</td>
                        <td class="right">Rs. {{ number_format($entry->balance_after) }}</td>
                </tr>@empty<tr>
                        <td colspan="6" class="empty">No transactions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $ledgers->links() }}</div>
@endsection