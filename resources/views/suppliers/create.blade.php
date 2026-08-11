@extends('backend.layout.layout')
@section('title', 'New Supplier · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>New supplier</h1>
            <p>Add a supplier to start tracking their khata.</p>
        </div><a class="button-light" href="{{ route('suppliers.index') }}">Back</a>
    </div>
    <form class="form-card" method="post" action="{{ route('suppliers.store') }}">@csrf
        <div class="form-grid">
            <label>Name<input name="name" value="{{ old('name') }}" required></label>
            <label>Phone<input name="phone" value="{{ old('phone') }}"></label>
            <label>Address<input name="address" value="{{ old('address') }}"></label>
            <label>Opening balance (agar pehle se udhar hai)<input type="number" step="0.01" name="opening_balance"
                    value="{{ old('opening_balance', 0) }}"></label>
        </div>
        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        <button class="primary">Save supplier</button>
    </form>
@endsection