@extends('backend.layout.layout')
@section('title', ($product->exists ? 'Edit' : 'Add') . ' Product ? Neerra')
@section('content')
<div class="page-head">
    <div>
        <h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
        <p>Set product prices, inventory and availability.</p>
    </div><a class="button-light" href="{{ route('products.index') }}">Back</a>
</div>
<form class="form-card" id="productForm" method="post"
    action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="form-grid">
        <label>Product name<input name="name" value="{{ old('name', $product->name) }}" required></label>
        <label>Size / unit<input name="size" value="{{ old('size', $product->size) }}" placeholder="e.g. 1 Litre, 1 Pack, 1 Piece" required></label>
        <label>Category<input name="category" list="productCategories" value="{{ old('category', $product->category) }}" placeholder="Choose or type a category" required></label>
        <label>Default price (Rs.)<input type="number" min="1" step="1" name="price" value="{{ old('price', $product->price ?? 10) }}" required></label>
        <label><span id="stockLabel">Available stock</span><input type="number" min="0" max="99999999.99" step="0.01" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required></label>
        <label class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>Available for sale</label>
    </div>
    <section id="biscuitStockFields" @if(! $product->isBiscuit() && ! preg_match('/biscuit|biscut|cookie/i', old('category', '').' '.old('name', ''))) hidden @endif>
        <h2>Biscuit stock by price</h2>
        <p>Keep one product. Enter the available pieces for each selling price. Total stock is calculated automatically.</p>
        @if($product->exists && $product->isBiscuit() && $product->price_stocks === null && $product->stock > 0)
            <p class="flash">Previous total: {{ $product->stock }} pieces. Enter the correct count for each price before selling this product.</p>
        @endif
        <div class="form-grid">
            @foreach(\App\Models\Product::BISCUIT_PRICES as $price)
                <label>Rs. {{ $price }} biscuits<input type="number" min="0" max="16666666" step="1" name="price_stocks[{{ $price }}]"
                    value="{{ old('price_stocks.'.$price, $product->price_stocks[$price] ?? ($product->exists && $product->price_stocks === null && $product->stock > 0 ? '' : 0)) }}"></label>
            @endforeach
        </div>
    </section>
    @if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
    <datalist id="productCategories">
        @foreach(['Milk', 'Water', 'Biscuits', 'Cakes', 'Bread', 'Snacks', 'Drinks', 'Grocery'] as $category)
            <option value="{{ $category }}"></option>
        @endforeach
    </datalist>
    <button class="primary">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
</form>
@endsection
@push('scripts')
    <script src="{{ asset('js/product-form.js') }}?v={{ filemtime(public_path('js/product-form.js')) }}"></script>
@endpush
