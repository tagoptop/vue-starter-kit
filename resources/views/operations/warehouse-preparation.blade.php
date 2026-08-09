@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Warehouse Preparation</h4>
        <p class="text-muted mb-0">Prepare delivery items by schedule and verify quantities before dispatch.</p>
    </div>
    <a href="{{ route('deliveries.index') }}" class="btn btn-outline-secondary">Open Delivery Monitoring</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-primary-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Orders In Queue</div>
                <div class="display-6 fw-semibold">{{ $summary['totalOrders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-info-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Items On This Page</div>
                <div class="display-6 fw-semibold">{{ $summary['totalItems'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-success-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Approved</div>
                <div class="display-6 fw-semibold">{{ $summary['approvedOrders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-warning-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Pending</div>
                <div class="display-6 fw-semibold">{{ $summary['pendingOrders'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('warehouse.preparation') }}" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label">Search Orders or Items</label>
                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Order #, customer, address, or item name">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Search</button>
                <a href="{{ route('warehouse.preparation') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($orders as $order)
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $order->order_number }}</h5>
                            <div class="small text-muted">Customer: {{ $order->customer?->name ?? 'Unknown' }}</div>
                            <div class="small text-muted">Delivery Date: {{ $order->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $order->status === 'approved' ? 'success' : 'warning text-dark' }}">{{ ucfirst($order->status) }}</span>
                            <div class="small text-muted mt-1">Driver: {{ $order->driver?->name ?? $order->driver_name ?? 'Unassigned' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-uppercase text-muted mb-1">Delivery Address</div>
                        <div>{{ $order->delivery_address ?: 'Not provided' }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Qty to Prepare</th>
                                    <th class="text-center">Prepared Mark</th>
                                    <th class="text-center">Action</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr class="{{ $item->is_prepared ? 'table-success' : '' }}">
                                        <td>{{ $item->product?->name ?? 'Unknown item' }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-center">
                                            @if($item->is_prepared)
                                                <span class="badge bg-success">Prepared</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(! $item->is_prepared)
                                                <form method="POST" action="{{ route('warehouse.preparation.items.mark-prepared', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success">Mark Prepared</button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Marked</span>
                                            @endif
                                        </td>
                                        <td class="text-end">₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end">₱{{ number_format((float) $item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="small text-muted">Total items: {{ $order->items->sum('quantity') }}</div>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Open Order Details</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">No orders found for warehouse preparation.</div>
        </div>
    @endforelse
</div>

@if($orders->hasPages())
    <div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
