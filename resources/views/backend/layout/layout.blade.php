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
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v={{ filemtime(public_path('css/pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}?v={{ filemtime(public_path('css/admin-theme.css')) }}">
    @yield('css')
</head>

<body>
    <svg class="icon-sprite" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <symbol id="icon-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
        <symbol id="icon-receipt" viewBox="0 0 24 24"><path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/></symbol>
        <symbol id="icon-box" viewBox="0 0 24 24"><path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/><path d="m3 7 9 4 9-4M12 11v10"/></symbol>
        <symbol id="icon-layers" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 16l9 5 9-5"/></symbol>
        <symbol id="icon-chart" viewBox="0 0 24 24"><path d="M4 20h16M6 16v-5M12 16V5M18 16V9"/></symbol>
        <symbol id="icon-truck" viewBox="0 0 24 24"><path d="M3 6h11v11H3V6ZM14 10h4l3 3v4h-7v-7Z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></symbol>
        <symbol id="icon-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2H3ZM16 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1h-3"/></symbol>
        <symbol id="icon-external" viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9"/><path d="M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/></symbol>
    </svg>
    <header class="mobile-bar">
        <button class="menu-toggle" type="button" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false"><span></span><span></span><span></span></button>
        <a class="mobile-brand" href="{{ route('pos') }}"><b>N</b><span>Neerra</span></a>
    </header>
    <div class="shell">
        <div class="sidebar-overlay" hidden></div>
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('pos') }}"><b>N</b><span>Neerra<small>BUSINESS STUDIO</small></span></a>
            <p class="side-label">WORKSPACE</p>
            <nav class="nav" aria-label="Main navigation">
                <a class="{{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}" title="Point of Sale" @if(request()->routeIs('pos')) aria-current="page" @endif><svg><use href="#icon-grid"/></svg><span>Point of Sale</span></a>
                <a class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}" title="Orders" @if(request()->routeIs('orders.*')) aria-current="page" @endif><svg><use href="#icon-receipt"/></svg><span>Orders</span></a>
                <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}" title="Products" @if(request()->routeIs('products.*')) aria-current="page" @endif><svg><use href="#icon-box"/></svg><span>Products</span></a>
                <a class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}" title="Purchase & Stock" @if(request()->routeIs('purchases.*')) aria-current="page" @endif><svg><use href="#icon-layers"/></svg><span>Purchase & Stock</span></a>
                <a class="{{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.sales') }}" title="Sales Report" @if(request()->routeIs('reports.*')) aria-current="page" @endif><svg><use href="#icon-chart"/></svg><span>Sales Report</span></a>
            </nav>
            <p class="side-label side-label-spaced">RELATIONSHIPS</p>
            <nav class="nav" aria-label="Contacts navigation">
                <a class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}" title="Suppliers" @if(request()->routeIs('suppliers.*')) aria-current="page" @endif><svg><use href="#icon-truck"/></svg><span>Suppliers</span></a>
                <a class="{{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}" title="Customers" @if(request()->routeIs('customers.*')) aria-current="page" @endif><svg><use href="#icon-users"/></svg><span>Customers</span></a>
            </nav>
            <div class="shop-card">
                <span class="account-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="account-details"><strong>{{ auth()->user()->name }}</strong><span>Administrator</span></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Sign out</button></form>
            </div>
        </aside>
        <div class="workspace">
            <div class="workspace-topbar">
                <div class="topbar-location"><span>NEERRA</span><i>/</i><strong>Workspace</strong></div>
                <a href="{{ route('shop') }}" target="_blank" rel="noopener noreferrer" class="shop-link">View storefront <svg><use href="#icon-external"/></svg></a>
            </div>
            <main class="content">
                @if (session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app-ui.js') }}?v={{ filemtime(public_path('js/app-ui.js')) }}"></script>
    @stack('scripts')
</body>

</html>
