@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Products</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" style="width: 50px; height: 50px; object-fit: cover;">
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name }}</td>
                        <td>{{ $product->supplier?->name }}</td>
                        <td>₱{{ number_format($product->price, 2) }}</td>
                        <td>
                            {{ $product->stock_quantity }}
                            @if($product->isLowStock())
                                <span class="badge bg-danger">Low</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
