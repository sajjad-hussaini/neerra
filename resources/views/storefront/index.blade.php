@php
    $catalog = $products->map(function ($product) {
        $water = str_contains(strtolower($product->category . ' ' . $product->name), 'water');
        return [
            'id' => $product->id, 'name' => $product->name, 'size' => $product->size,
            'category' => $product->category, 'price' => (float) $product->price,
            'availableStock' => max(0, (int) floor((float) $product->stock)),
            'max' => min(100, max(0, (int) floor((float) $product->stock))),
            'image' => asset('images/store/' . ($water ? 'drinking-water.jpg' : 'fresh-milk.jpg')),
        ];
    })->values();
    $categories = $catalog->pluck('category')->unique()->values();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Shop fresh milk and drinking water at Neerra. Browse everyday essentials, fill your basket and order for delivery.">
    <meta name="theme-color" content="#234b3d">
    <title>Neerra — Fresh essentials for your everyday</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}">
    <link rel="preload" as="image" href="{{ asset('images/store/morning-dairy.jpg') }}">
</head>
<body>
    <a href="#products" class="skip-link">Skip to products</a>
    <svg class="icon-library" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <symbol id="i-bag" viewBox="0 0 24 24"><path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></symbol>
        <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol>
        <symbol id="i-leaf" viewBox="0 0 24 24"><path d="M20 3C9 2 3 7 5 14c2 7 14 6 15-11Z"/><path d="M4 21 15 10"/></symbol>
        <symbol id="i-truck" viewBox="0 0 24 24"><path d="M2 5h12v13H2V5Zm12 5h4l4 4v4h-8v-8Z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
        <symbol id="i-wallet" viewBox="0 0 24 24"><path d="M20 7H4V4h14v3M4 7a2 2 0 0 1-2-2v14a2 2 0 0 0 2 2h16V7Z"/><path d="M20 11h-6v6h6M16 14h1"/></symbol>
        <symbol id="i-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M5 12h14M12 5v14"/></symbol>
        <symbol id="i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></symbol>
        <symbol id="i-box" viewBox="0 0 24 24"><path d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10"/></symbol>
    </svg>

    <div class="announcement"><div class="shop-container"><span>Good mornings start with fresh essentials.</span><a href="#how">A simpler way to shop <span aria-hidden="true">↗</span></a></div></div>
    <header class="store-header">
        <div class="shop-container header-main">
            <a class="store-brand" href="{{ route('shop') }}" aria-label="Neerra home"><svg class="brand-leaf" aria-hidden="true"><use href="#i-leaf"/></svg><span>neerra<span class="brand-dot">.</span><small>FRESH FOR YOUR EVERYDAY</small></span></a>
            <form class="store-search" role="search" id="searchForm">
                <svg class="icon" aria-hidden="true"><use href="#i-search"/></svg>
                <input id="productSearch" type="search" placeholder="Search milk, water and more…" aria-label="Search products" autocomplete="off">
                <kbd aria-hidden="true">Search</kbd>
            </form>
            <button class="header-basket" type="button" data-open-basket aria-controls="cart" aria-label="Open shopping basket">
                <span class="basket-icon"><svg class="icon" aria-hidden="true"><use href="#i-bag"/></svg><span id="cartCount">0</span></span>
                <span class="basket-label">Your basket<strong id="headerCartTotal">Rs. 0</strong></span>
            </button>
        </div>
        <nav class="store-nav shop-container" aria-label="Store navigation">
            <div><a href="#products" class="nav-shop">Shop all essentials</a>@foreach($categories->take(3) as $category)<button type="button" data-category-link="{{ $category }}">{{ $category }}</button>@endforeach</div>
            <div><a href="#how">How to order</a><a href="#help">Need a hand?</a></div>
        </nav>
    </header>

    <main id="top">
        <section class="store-hero shop-container" aria-labelledby="heroTitle">
            <div class="hero-copy">
                <p class="eyebrow"><span></span> YOUR DAILY DOSE OF GOODNESS</p>
                <h1 id="heroTitle">Fresh essentials.<br>A happier <em>everyday.</em></h1>
                <p class="hero-description">From the first cup of chai to the last glass of water. Bring home the little things that make every day better.</p>
                <div class="hero-actions"><a href="#products" class="store-button">Shop the essentials<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a><a href="#how" class="underlined-link">How it works</a></div>
                <div class="hero-footnote"><svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg><span>Milk, water & a little everyday goodness.</span></div>
            </div>
            <div class="hero-photo">
                <img src="{{ asset('images/store/morning-dairy.jpg') }}" alt="A sunlit breakfast table with milk and yogurt" fetchpriority="high" width="1536" height="1024">
                <div class="photo-label"><span class="photo-label-icon"><svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg></span><div><small>THE SIMPLE THINGS</small><strong>Made for your daily routine.</strong></div></div>
                <span class="hero-photo-note">A fresh start, every day.</span>
            </div>
        </section>

        <section class="store-benefits shop-container" aria-label="Shopping benefits">
            <div><svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg><p><strong>Everyday essentials</strong><span>Milk & water for your home</span></p></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-truck"/></svg><p><strong>Doorstep delivery</strong><span>Order from wherever you are</span></p></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-wallet"/></svg><p><strong>Cash on delivery</strong><span>Choose your payment at checkout</span></p></div>
            <div><svg class="icon" aria-hidden="true"><use href="#i-bag"/></svg><p><strong>Shop without an account</strong><span>A few details, and you're done</span></p></div>
        </section>

        <section class="catalog-section shop-container" id="products" aria-labelledby="catalogTitle">
            <div class="section-heading"><div><p class="eyebrow">THE NEERRA SHELF</p><h2 id="catalogTitle">Good things for every day.</h2><p>Pick your favourites. We'll take it from here.</p></div><span class="catalog-count" id="productCount" aria-live="polite">{{ $catalog->count() }} products</span></div>
            <div class="catalog-toolbar">
                <div class="category-tabs" role="group" aria-label="Filter by category"><button class="selected" type="button" data-category="" aria-pressed="true">All essentials<span>{{ $catalog->count() }}</span></button>@foreach($categories as $category)<button type="button" data-category="{{ $category }}" aria-pressed="false">{{ $category }}</button>@endforeach</div>
                <label class="catalog-sort"><span>Sort by</span><select id="productSort" aria-label="Sort products"><option value="featured">Recommended</option><option value="price-low">Price: low to high</option><option value="price-high">Price: high to low</option><option value="name">Name: A to Z</option></select></label>
            </div>
            <div class="store-product-grid" id="productGrid">
                @foreach($catalog as $product)
                    <article class="store-product" data-product-id="{{ $product['id'] }}">
                        <div class="product-visual"><img src="{{ $product['image'] }}" alt="{{ $product['category'] }} category image" loading="lazy" width="1254" height="1254"><span class="availability" data-availability="{{ $product['id'] }}">{{ $product['max'] > 0 ? 'In stock' : 'Unavailable' }}</span><small>Illustrative image</small></div>
                        <div class="product-info"><span class="product-category">{{ $product['category'] }}</span><h3>{{ $product['name'] }}</h3><p class="product-unit">{{ $product['size'] }}</p><div class="product-buy-row"><div class="product-price">Rs. {{ number_format($product['price']) }}<small>per {{ $product['size'] }}</small></div><div class="product-controls" data-product-controls="{{ $product['id'] }}"><button class="add-product" type="button" data-cart-action="add" data-product="{{ $product['id'] }}" @disabled($product['max'] < 1)>Add <span aria-hidden="true">+</span></button></div></div></div>
                    </article>
                @endforeach
            </div>
            <div class="catalog-empty" id="catalogEmpty" @if($catalog->isNotEmpty()) hidden @endif><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg><h3>{{ $catalog->isEmpty() ? 'The shelf is being restocked.' : 'No products found.' }}</h3><p>{{ $catalog->isEmpty() ? 'Please check back soon for fresh essentials.' : 'Try another search or browse all our essentials.' }}</p><button type="button" class="store-button" id="resetFilters" @if($catalog->isEmpty()) hidden @endif>Browse all essentials</button></div>
        </section>

        <section class="everyday-section shop-container" aria-labelledby="everydayTitle"><div class="everyday-photo"><img src="{{ asset('images/store/fresh-milk.jpg') }}" alt="Fresh milk in a bottle and glass" width="1254" height="1254" loading="lazy"></div><div class="everyday-copy"><p class="eyebrow">LESS TO THINK ABOUT. MORE TO ENJOY.</p><h2 id="everydayTitle">A full fridge.<br>A simpler day.</h2><p>Some things are part of every home. Your morning chai. A glass of milk. Water at the dinner table.</p><p>Neerra brings those everyday essentials together, so your next refill is only a few taps away.</p><a href="#products" class="underlined-link">Find your daily favourites <span aria-hidden="true">↗</span></a></div></section>

        <section class="how-section shop-container" id="how" aria-labelledby="howTitle"><div class="section-heading"><div><p class="eyebrow">FROM OUR SHELF TO YOUR DOOR</p><h2 id="howTitle">Your next refill, made simple.</h2></div></div><div class="ordering-steps"><article><span>01</span><svg class="icon" aria-hidden="true"><use href="#i-bag"/></svg><h3>Fill your basket</h3><p>Choose your essentials and the quantities your home needs.</p></article><article><span>02</span><svg class="icon" aria-hidden="true"><use href="#i-box"/></svg><h3>Make it yours</h3><p>Add your delivery address and choose a payment method.</p></article><article><span>03</span><svg class="icon" aria-hidden="true"><use href="#i-truck"/></svg><h3>Leave the rest to us</h3><p>Place your order and keep the reference for your delivery.</p></article></div></section>

        <section class="help-section shop-container" id="help" aria-labelledby="helpTitle"><div><p class="eyebrow">A LITTLE HELP</p><h2 id="helpTitle">Before you<br>fill your basket.</h2><p>A few things you might want to know.</p></div><div class="store-faq"><details><summary>Do I need an account to order?</summary><p>No account is needed. Add your products, enter your name, phone number and delivery address, then place your order.</p></details><details><summary>How can I pay for my order?</summary><p>You can select cash on delivery or online payment at checkout. Choose your preferred method before placing your order.</p></details><details><summary>Can I change the quantities in my basket?</summary><p>Yes. Use the plus and minus buttons in your basket to adjust quantities, or remove an item before checkout.</p></details></div></section>
    </main>

    <footer class="store-footer"><div class="shop-container footer-main"><div><a class="store-brand" href="#top"><svg class="brand-leaf" aria-hidden="true"><use href="#i-leaf"/></svg><span>neerra<span class="brand-dot">.</span></span></a><p>A little freshness.<br>A lot of everyday goodness.</p></div><div><h3>Explore Neerra</h3><a href="#products">Shop all essentials</a><a href="#how">How to order</a><a href="#help">Shopping help</a></div><div class="footer-note"><svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg><p>The little things<br>make a home.</p></div></div><div class="shop-container footer-bottom"><span>© {{ now()->year }} Neerra. All rights reserved.</span><span>Prices in PKR · Milk & water, delivered.</span></div></footer>

    <button class="mobile-basket" id="mobileBasket" type="button" data-open-basket hidden><span><svg class="icon" aria-hidden="true"><use href="#i-bag"/></svg><span id="mobileCartCount">0 items</span></span><strong>View basket <span id="mobileCartTotal">Rs. 0</span> <span aria-hidden="true">→</span></strong></button>
    <div class="offcanvas offcanvas-end basket-drawer" tabindex="-1" id="cart" aria-labelledby="basketTitle">
        <div class="offcanvas-header"><div><p class="eyebrow">YOUR DAILY GOODNESS</p><h2 id="basketTitle">Your basket <span id="basketItemCount">0</span></h2></div><button type="button" class="icon-button" data-close-overlay aria-label="Close basket"><svg class="icon" aria-hidden="true"><use href="#i-close"/></svg></button></div>
        <div class="offcanvas-body"><div id="cartItems" class="cart-items"></div><div class="cart-summary"><div><span>Subtotal</span><strong id="cartTotal">Rs. 0</strong></div><p>Review your items, then add your delivery details.</p><button class="store-button" id="checkoutButton" type="button" disabled>Continue to checkout<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button><span class="checkout-note"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg> No account needed to place an order</span></div></div>
    </div>

    <div class="modal fade checkout-modal" id="checkout" tabindex="-1" aria-labelledby="checkoutTitle"><div class="modal-dialog modal-dialog-centered modal-lg"><form class="modal-content" id="orderForm"><div id="checkoutFields"><div class="modal-header"><div><p class="eyebrow">ONE LAST THING</p><h2 id="checkoutTitle">Let's bring it home.</h2></div><button type="button" class="icon-button" data-close-overlay aria-label="Close checkout"><svg class="icon" aria-hidden="true"><use href="#i-close"/></svg></button></div><div class="checkout-layout"><div class="checkout-details"><h3>Your delivery details</h3><label for="customerName">Full name<input id="customerName" name="customer_name" autocomplete="name" maxlength="100" placeholder="Your full name" required></label><label for="customerPhone">Phone number<input id="customerPhone" type="tel" name="customer_phone" autocomplete="tel" maxlength="30" placeholder="03XX XXXXXXX" required></label><label for="deliveryAddress">Delivery address<textarea id="deliveryAddress" name="delivery_address" autocomplete="street-address" maxlength="1000" placeholder="House number, street, area and city" rows="3" required></textarea></label><fieldset class="payment-options"><legend>How would you like to pay?</legend><label><input type="radio" name="payment_method" value="Cash" checked><span>Cash on delivery</span></label><label><input type="radio" name="payment_method" value="Online"><span>Online payment</span></label></fieldset></div><aside class="checkout-review"><h3>Order summary</h3><div id="checkoutItems"></div><div class="checkout-total"><span>Subtotal</span><strong id="checkoutTotal">Rs. 0</strong></div><p>Your order will be sent with the delivery details you've entered.</p></aside></div><div class="order-error" id="orderError" role="alert" hidden></div><div class="modal-footer"><span>Ready for a little everyday goodness?</span><button class="store-button" id="placeOrder" type="submit">Place order<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button></div></div><div class="order-success" id="orderSuccess" hidden><span class="success-mark"><svg class="icon" aria-hidden="true"><use href="#i-check"/></svg></span><p class="eyebrow">THANK YOU FOR SHOPPING WITH NEERRA</p><h2>Your order is in.</h2><p>We've received your order. Keep this reference for your delivery.</p><div class="order-reference"><small>ORDER REFERENCE</small><strong id="orderReference"></strong></div><button class="store-button" type="button" data-close-overlay>Continue shopping<svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button></div></form></div></div>
    <div class="store-backdrop" id="storeBackdrop" hidden></div>
    <div class="store-notification" id="storeNotification" role="status" aria-live="polite"></div>
    <script>window.storeConfig = { orderUrl: @json(route('shop.orders.store')), products: @json($catalog) };</script>
    <script src="{{ asset('js/storefront.js') }}?v={{ filemtime(public_path('js/storefront.js')) }}"></script>
</body>
</html>
