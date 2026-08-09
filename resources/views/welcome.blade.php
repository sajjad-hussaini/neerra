@extends('backend.layout.layout')
@section('title', 'Point of Sale · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Point of Sale</h1>
            <p>Build a new order and collect payment.</p>
        </div>
        <div class="date" id="today"></div>
    </div>
    <section class="stats">
        <div class="stat"><small>TODAY'S SALES</small><strong id="dailySales">Rs. 0</strong></div>
        <div class="stat"><small>ORDERS TODAY</small><strong id="orderCount">0</strong></div>
        <div class="stat"><small>THIS MONTH</small><strong id="monthSales">Rs. 0</strong></div>
        <div class="stat"><small>THIS YEAR</small><strong id="yearSales">Rs. 0</strong></div>
    </section>
    <div class="pos-grid">
        <section>
            <div class="section-head">
                <div>
                    <h2>Products</h2>
                    <p>Choose an item to add it to the order.</p>
                </div><a class="text-link" href="{{ route('products.index') }}">Manage products →</a>
            </div><label class="search">⌕<input id="search" type="search" placeholder="Search products..."></label>
            <div class="products" id="products"></div>
        </section>
        <aside class="cart">
            <div class="cart-top">
                <h2>Current Order</h2><span class="pill" id="cartCount">0 items</span>
            </div>
            <div class="order-items" id="orderItems">
                <div class="empty">Your cart is empty.<br>Add products to start a sale.</div>
            </div>
            <div class="summary">
                <div><span>Subtotal</span><span id="subtotal">Rs. 0</span></div>
                <div class="total"><span>Total</span><span id="total">Rs. 0</span></div>
                <div class="pay-methods"><button class="method selected" data-method="Cash">Cash</button><button
                        class="method" data-method="Card">Card</button><button class="method"
                        data-method="Online">Online</button></div><button class="primary full" id="checkout"
                    disabled>Complete Sale</button><button class="clear" id="clear">Clear order</button>
            </div>
        </aside>
    </div>
    <div class="toast" id="toast"></div>
@endsection
@push('scripts')
    <script>
        window.posConfig = {
            products: @json($products),
            storeUrl: '{{ route('sales.store') }}',
            statsUrl: '{{ route('sales.stats') }}'
        };
    </script>
    <script src="{{ asset('js/pos.js') }}"></script>
@endpush
