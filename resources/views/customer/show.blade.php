@extends('backend.layout.layout')
@section('title', $customer->name . ' · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $customer->name }}</h1>
            <p>{{ $customer->phone ?: '—' }}</p>
        </div>
        <a class="button-light" href="{{ route('customers.index') }}">Back</a>
    </div>

    <div class="stats-row">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Total Udhar</div>
                        <div class="fs-4 fw-bold">Rs. {{ number_format($totalUdhar) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted small">Total Paid</div>
                        <div class="fs-4 fw-bold">Rs. {{ number_format($totalPaid) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-danger">
                    <div class="card-body">
                        <div class="text-muted small">Outstanding Balance</div>
                        <div class="fs-4 fw-bold text-danger">Rs. {{ number_format($customer->currentBalance()) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">Add Udhar</h6>
                        <form method="post" action="{{ route('customers.credit', $customer) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <input name="description" class="form-control" placeholder="e.g. Sale on credit">
                            </div>
                            <button class="btn btn-primary w-100">Add Udhar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">Record Payment</h6>
                        <form method="post" action="{{ route('customers.payment', $customer) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <input name="note" class="form-control" placeholder="e.g. Cash received">
                            </div>
                            <button class="btn btn-success w-100">Record Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="right">Udhar</th>
                    <th class="right">Paid</th>
                    <th class="right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgers as $entry)
                    <tr>
                        <td>{{ $entry->transaction_date->format('d M Y') }}</td>
                        <td>{{ ucfirst($entry->type) }}</td>
                        <td>{{ $entry->description ?: '—' }}</td>
                        <td class="right">{{ $entry->debit ? 'Rs. ' . number_format($entry->debit) : '—' }}</td>
                        <td class="right">{{ $entry->credit ? 'Rs. ' . number_format($entry->credit) : '—' }}</td>
                        <td class="right">Rs. {{ number_format($entry->balance_after) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">No transactions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $ledgers->links() }}</div>
@endsection