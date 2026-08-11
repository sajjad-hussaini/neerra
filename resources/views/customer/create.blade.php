@extends('backend.layout.layout')
@section('title', 'New Purchase · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>New purchase</h1>
            <p>Stock is updated when you save this purchase.</p>
        </div><a class="button-light" href="{{ route('purchases.index') }}">Back</a>
    </div>
    <form class="form-card purchase-form" method="post" action="{{ route('purchases.store') }}">@csrf
        <div class="form-grid"><label>Supplier (optional)<input name="supplier" value="{{ old('supplier') }}"
                    placeholder="Supplier name"></label><label>Purchase date<input type="date" name="purchased_at"
                    value="{{ old('purchased_at', now()->toDateString()) }}" required></label></div>
        <div class="purchase-head">
            <h2>Items</h2><button class="button-light" type="button" id="addPurchaseRow">+ Add item</button>
        </div>
        <div id="purchaseRows"></div>
        @if ($errors->any())
            <div class="errors">{{ $errors->first() }}</div>
        @endif
        <div class="purchase-total">Estimated total <strong id="purchaseTotal">Rs. 0</strong></div><button
            class="primary">Save purchase & update stock</button>
    </form>
@endsection
@push('scripts')
    <script>
        window.purchaseProducts = @json($products);
    </script>
    <script src="{{ asset('js/purchases.js') }}"></script>
@endpush
