<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Neerra | Fresh Dairy Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light py-3 sticky-top">
        <div class="container"><a class="navbar-brand" href="#top"><span>NR</span> Neerra</a><button
                class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#products">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">How it works</a></li>
                    <li class="nav-item ms-lg-3"><button class="cart-button" data-bs-toggle="offcanvas"
                            data-bs-target="#cart">Cart <span id="cartCount">0</span></button></li>
                </ul>
            </div>
        </div>
    </nav>
    <main id="top">
        <section class="hero">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <p class="badge-copy">FRESHNESS DELIVERED TO YOU</p>
                        <h1>Everyday essentials, <em>fresh</em> at your door.</h1>
                        <p class="lead">Order fresh milk and mineral water online. Simple checkout, dependable
                            delivery.</p><a href="#products" class="btn hero-button">Shop now</a>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-art">
                            <div class="milk-card">MILK<br><small>Fresh daily</small></div>
                            <div class="water-card">WATER<br><small>Pure & chilled</small></div>
                            <div class="hero-circle"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="container py-5" id="products">
            <div class="section-title">
                <div>
                    <p class="badge-copy">SHOP ONLINE</p>
                    <h2>Fresh products for your home</h2>
                </div>
                <p>Prices shown in PKR</p>
            </div>
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-sm-6 col-lg-4">
                        <article class="product-card">
                            <div class="store-icon {{ strtolower($product->category) === 'water' ? 'water' : '' }}">
                                {{ strtolower($product->category) === 'water' ? 'WATER' : 'MILK' }}</div>
                            <div class="product-body"><span class="category">{{ $product->category }}</span>
                                <h3>{{ $product->name }}</h3>
                                <p>{{ $product->size }}</p>
                                <div class="d-flex justify-content-between align-items-center"><strong>Rs.
                                        {{ number_format($product->price) }}</strong><button class="add-cart"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-size="{{ $product->size }}" data-price="{{ $product->price }}">Add
                                        +</button></div>
                            </div>
                        </article>
                </div>@empty<div class="col-12">
                        <p class="text-center text-muted py-5">Products will be available soon.</p>
                    </div>
                @endforelse
            </div>
        </section>
        <section id="how" class="steps">
            <div class="container">
                <div class="text-center mb-5">
                    <p class="badge-copy">EASY ORDERING</p>
                    <h2>Three simple steps</h2>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div><b>01</b>
                            <h3>Choose products</h3>
                            <p>Add your everyday essentials to the cart.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div><b>02</b>
                            <h3>Enter delivery details</h3>
                            <p>Tell us where to deliver your order.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div><b>03</b>
                            <h3>Receive fresh goods</h3>
                            <p>We will prepare and deliver your order.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="container d-flex justify-content-between flex-wrap gap-2"><strong>Neerra</strong><span>Freshness for
                every home.</span></div>
    </footer>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cart">
        <div class="offcanvas-header">
            <h5>Your cart</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div id="cartItems" class="cart-items">
                <p class="empty-cart">Your cart is empty.</p>
            </div>
            <div class="cart-summary">
                <div><span>Subtotal</span><strong id="cartTotal">Rs. 0</strong></div><button class="btn checkout-button"
                    id="checkoutButton" disabled data-bs-toggle="modal" data-bs-target="#checkout">Checkout</button>
            </div>
        </div>
    </div>
    <div class="modal fade" id="checkout" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="orderForm">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Delivery details</h5><small>Complete your online order</small>
                    </div><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Full name</label><input class="form-control"
                                name="customer_name" required></div>
                        <div class="col-12"><label class="form-label">Phone number</label><input class="form-control"
                                name="customer_phone" required placeholder="03XX XXXXXXX"></div>
                        <div class="col-12"><label class="form-label">Delivery address</label>
                            <textarea class="form-control" name="delivery_address" rows="3" required></textarea>
                        </div>
                        <div class="col-12"><label class="form-label">Payment method</label><select
                                class="form-select" name="payment_method">
                                <option value="Cash">Cash on delivery</option>
                                <option value="Online">Online payment</option>
                            </select></div>
                    </div>
                    <div class="order-error d-none" id="orderError"></div>
                </div>
                <div class="modal-footer"><button class="btn checkout-button w-100" id="placeOrder">Place
                        order</button></div>
            </form>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="orderToast" class="toast border-0" role="alert">
            <div class="toast-body"></div>
        </div>
    </div>
    <script>
        window.storeConfig = {
            orderUrl: '{{ route('shop.orders.store') }}'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}"></script>
</body>

</html>
