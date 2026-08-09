@extends('backend.layout.layout')
@section('title', 'Products · Neerra')
@section('content')
    <div class="page-head">
        <div>
            <h1>Products</h1>
            <p>Manage prices, inventory and availability.</p>
        </div><a class="primary" href="{{ route('products.create') }}">+ Add product</a>
    </div>
    <form class="filter" method="get"><input name="search" value="{{ request('search') }}"
            placeholder="Search name or category"><button>Search</button></form>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><strong>{{ $product->name }}</strong><small>{{ $product->size }}</small></td>
                        <td>{{ $product->category }}</td>
                        <td>Rs. {{ number_format($product->price) }}</td>
                        <td><span class="{{ $product->stock < 10 ? 'low-stock' : '' }}">{{ $product->stock }} units</span>
                        </td>
                        <td><span
                                class="status {{ $product->is_active ? 'success' : 'muted' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="actions"><a href="{{ route('products.edit', $product) }}">Edit</a>
                            <form method="post" action="{{ route('products.destroy', $product) }}">@csrf
                                @method('DELETE')<button class="danger" data-confirm="Remove this product?">Delete</button>
                            </form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="6" class="empty">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $products->links() }}</div>
@endsection
