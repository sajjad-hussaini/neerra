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
                </div>
                <div class="pos-product-actions">
                    <a class="button-light" href="{{ route('products.create') }}">+ Add product</a>
                    <a class="text-link" href="{{ route('products.index') }}">Manage products</a>
                </div>
            </div><label class="search">⌕<input id="search" type="search" placeholder="Search products..."></label>
            <section class="biscuit-prices" id="biscuitPrices" aria-labelledby="biscuitPriceName" hidden>
                <div class="biscuit-price-heading">
                    <strong id="biscuitPriceName"></strong>
                    <button type="button" class="button-light" id="closeBiscuitPrices" aria-label="Close biscuit prices">Close</button>
                </div>
                <p>Choose a biscuit price. Each click adds one biscuit at that price.</p>
                <div class="biscuit-price-options" id="biscuitPriceOptions" role="group" aria-label="Biscuit amounts"></div>
                <p class="biscuit-price-status" id="biscuitPriceStatus" role="status" aria-live="polite"></p>
            </section>
            <div class="products" id="products"></div>
        </section>
        <aside class="cart">
            <div class="cart-top">
                <h2>Current Order</h2><span class="pill" id="cartCount">0 items</span>
            </div>
            <div class="customer-bar">
                <label class="customer-select">
                    <span>Customer</span>
                    <select id="customerSelect">
                        <option value="">Walk-in (cash)</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}{{ $c->phone ? ' · ' . $c->phone : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="button" class="button-light" id="newCustomerBtn">+ New</button>
            </div>
            <div class="new-customer" id="newCustomerForm" hidden>
                <input id="newCustomerName" placeholder="Customer name" autocomplete="off">
                <input id="newCustomerPhone" placeholder="Phone (optional)" autocomplete="off">
                <div class="new-customer-actions">
                    <button type="button" class="primary" id="saveCustomerBtn">Save</button>
                    <button type="button" class="clear" id="cancelCustomerBtn">Cancel</button>
                </div>
            </div>
            <div class="order-items" id="orderItems">
                <div class="empty">Your cart is empty.<br>Add products to start a sale.</div>
            </div>
            <div class="summary">
                <div><span>Subtotal</span><span id="subtotal">Rs. 0</span></div>
                <div class="discount-toggle">
                    <label for="discountEnabled"><input type="checkbox" id="discountEnabled" aria-controls="discountRow" aria-expanded="false"> Apply discount</label>
                </div>
                <div class="discount-row" id="discountRow" hidden>
                    <label for="discountAmount">Discount (Rs.)</label>
                    <span class="paid-input">Rs. <input type="number" id="discountAmount" min="0" step="1" inputmode="numeric" value="" disabled></span>
                </div>
                <div class="total"><span>Total</span><span id="total">Rs. 0</span></div>
                <div class="paid-row" id="paidRow" hidden>
                    <span>Paid now</span>
                    <span class="paid-input">Rs. <input type="number" id="paidAmount" min="0" step="1" inputmode="numeric"></span>
                </div>
                <div class="due-row" id="dueRow" hidden><span>Udhaar (Due)</span><span id="dueAmount">Rs. 0</span></div>
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
            customers: @json($customers),
            storeUrl: '{{ route('sales.store') }}',
            statsUrl: '{{ route('sales.stats') }}',
            customerStoreUrl: '{{ route('customers.store') }}'
        };
    </script>
    <script src="{{ asset('js/pos.js') }}?v={{ filemtime(public_path('js/pos.js')) }}"></script>
@endpush
