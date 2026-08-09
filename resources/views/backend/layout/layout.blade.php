<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Neerra')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
</head>

<body>
    <div class="shell">
        <aside class="sidebar"><a class="brand" href="{{ route('pos') }}"><b>NR</b><span>Neerra</span></a>
            <p class="side-label">MAIN MENU</p>
            <nav class="nav"><a class="{{ request()->routeIs('pos') ? 'active' : '' }}"
                    href="{{ route('pos') }}">POS <span>Point of Sale</span></a><a
                    class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">ORD
                    <span>Orders</span></a><a class="{{ request()->routeIs('products.*') ? 'active' : '' }}"
                    href="{{ route('products.index') }}">PRD <span>Products</span></a><a
                    class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}"
                    href="{{ route('purchases.index') }}">STK <span>Purchase & Stock</span></a><a
                    class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
                    href="{{ route('reports.sales') }}">REP <span>Sales Report</span></a></nav>
            <div class="shop-card"><strong>{{ auth()->user()->name }}</strong>Signed in as admin<form method="post"
                    action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Sign out</button></form>
            </div>
        </aside>
        <main class="content">
            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif @yield('content')
        </main>
    </div>
    <script src="{{ asset('js/app-ui.js') }}"></script>@stack('scripts')
</body>

</html>
