@extends('backend.layout.layout')
@section('title', 'New Purchase · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>New Customer</h1>
            <p>Add a customer to start tracking their khata.</p>
        </div><a class="button-light" href="{{ route('customers.index') }}">Back</a>
    </div>
    <form class="form-card" method="post" action="{{ route('customers.store') }}">@csrf
        <div class="form-grid">
            <label>Name<input name="name" value="{{ old('name') }}" required></label>
            <label>Phone<input name="phone" value="{{ old('phone') }}"></label>
        </div>
        <button class="button-primary" type="submit">Create Customer</button>
    </form>
@endsection
@push('scripts')
    <script>
    </script>
    <script src="{{ asset('js/purchases.js') }}"></script>
@endpush
