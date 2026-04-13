@extends('layouts.app')

@section('content')
<h4 class="mb-3">Edit Product</h4>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" min="0" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" min="0" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
