@extends('backend.layout.layout')
@section('title', 'Suppliers · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Suppliers</h1>
            <p>Manage suppliers and their khata.</p>
        </div><a class="primary" href="{{ route('suppliers.create') }}">+ New supplier</a>
    </div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Purchases</th>
                    <th class="right">Balance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td><a href="{{ route('suppliers.show', $supplier) }}"><strong>{{ $supplier->name }}</strong></a></td>
                        <td>{{ $supplier->phone ?: '—' }}</td>
                        <td>{{ $supplier->purchases_count }}</td>
                        <td class="right">Rs. {{ number_format($supplier->currentBalance()) }}</td>
                        <td>
                            <form method="post" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier? Past purchases and khata records will be kept.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="record-delete">Delete</button>
                            </form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5" class="empty">No suppliers added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $suppliers->links() }}</div>
@endsection
