@extends('layouts.app')

@section('content')
<h4 class="mb-3">Inventory Management</h4>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Stock In</h6>
                <form action="{{ route('inventory.stock-in') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <select name="product_id" class="form-select" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" min="1" name="quantity" class="form-control" placeholder="Quantity" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                    </div>
                    <button class="btn btn-success">Record Stock In</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6>Stock Out</h6>
                <form action="{{ route('inventory.stock-out') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <select name="product_id" class="form-select" required>
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" min="1" name="quantity" class="form-control" placeholder="Quantity" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                    </div>
                    <button class="btn btn-danger">Record Stock Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($lowStockProducts->isNotEmpty())
    <div class="alert alert-warning">
        <strong>Low stock alert:</strong>
        {{ $lowStockProducts->pluck('name')->join(', ') }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Reference</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $transaction->product?->name }}</td>
                        <td>
                            <span class="badge {{ $transaction->type === 'in' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($transaction->type) }}</span>
                        </td>
                        <td>{{ $transaction->quantity }}</td>
                        <td>{{ $transaction->reference }}</td>
                        <td>{{ $transaction->user?->name ?? 'System' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
