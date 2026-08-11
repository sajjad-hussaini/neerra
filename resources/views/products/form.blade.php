@extends('backend.layout.layout')
@section('title', ($product->exists ? 'Edit' : 'Add') . ' Product · Neerra')
@section('content')
<div class="page-head">
    <div>
        <h1>{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
        <p>Set product details and inventory.</p>
    </div><a class="button-light" href="{{ route('products.index') }}">← Back</a>
</div>
<form class="form-card" method="post"
    action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}">@csrf @if ($product->exists)
    @method('PUT')
    @endif
    <div class="form-grid"><label>Product name<input name="name" value="{{ old('name', $product->name) }}"
                required></label><label>Size / unit<input name="size" value="{{ old('size', $product->size) }}"
                placeholder="e.g. 1 Litre" required></label><label>Category<input name="category"
                value="{{ old('category', $product->category) }}" placeholder="Milk, Water..."
                required></label><label>Price (Rs.)<input type="number" min="1" name="price"
                value="{{ old('price', $product->price) }}" required></label><label>Available stock<input type="number"
                min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required></label><label
            class="checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
            Available for sale</label></div>
    @if ($errors->any())
    <div class="errors">{{ $errors->first() }}</div>
    @endif
    <button class="primary">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
</form>
@endsection