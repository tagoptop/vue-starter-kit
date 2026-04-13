@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Products</h6>
                <h3>{{ $totalProducts }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Orders</h6>
                <h3>{{ $totalOrders }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Customers</h6>
                <h3>{{ $totalCustomers }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Sales Summary</h6>
                <h3>₱{{ number_format($salesSummary, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-body">
        <h5 class="mb-2">Inventory Alerts</h5>
        <p class="mb-0">Low stock products: <strong>{{ $lowStockCount }}</strong></p>
    </div>
</div>
@endsection
