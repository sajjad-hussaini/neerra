@extends('backend.layout.layout')
@section('title', 'Customers · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Customers</h1>
            <p>{{ $customers->total() }} total</p>
        </div>
        <a href="{{ route('customers.create') }}">
            <button class="primary">
                + Add Customer
            </button>
        </a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th class="right">Total Udhar</th>
                    <th class="right">Total Paid</th>
                    <th class="right">Balance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?: '—' }}</td>
                        <td class="right">Rs. {{ number_format($customer->total_udhar ?? 0) }}</td>
                        <td class="right">Rs. {{ number_format($customer->total_paid ?? 0) }}</td>
                        <td class="right">
                            <strong class="{{ $customer->currentBalance() > 0 ? 'text-danger' : '' }}">
                                Rs. {{ number_format($customer->currentBalance()) }}
                            </strong>
                        </td>
                        <td><a class="text-link"  href="{{ route('customers.show', $customer) }}">View →</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">No customers yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $customers->links() }}</div>

    <div id="add-customer-modal" class="modal">
        <div class="modal-box">
            <h2>Add Customer</h2>
            <form method="post" action="{{ route('customers.store') }}">
                @csrf
                <div class="form-grid">
                    <label>Name<input name="name" required></label>
                    <label>Phone<input name="phone"></label>
                    <label>Address<input name="address"></label>
                    <label>Opening balance<input type="number" step="0.01" name="opening_balance" value="0"></label>
                </div>
                <button class="primary">Save</button>
            </form>
        </div>
    </div>
@endsection