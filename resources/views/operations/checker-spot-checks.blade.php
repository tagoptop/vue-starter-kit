@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Checker Spot Checks</h4>
        <p class="text-muted mb-0">Perform on-the-spot delivery checks for assigned driver, destination, and item completeness.</p>
    </div>
    <a href="{{ route('deliveries.index') }}" class="btn btn-outline-secondary">Open Delivery Monitoring</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-primary-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Orders To Check</div>
                <div class="display-6 fw-semibold">{{ $summary['totalOrders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-info-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">With Coordinates</div>
                <div class="display-6 fw-semibold">{{ $summary['withCoordinates'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-warning-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Approved</div>
                <div class="display-6 fw-semibold">{{ $summary['approvedOrders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-success-subtle h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Delivered</div>
                <div class="display-6 fw-semibold">{{ $summary['deliveredOrders'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('checker.spot-checks') }}" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label">Search Orders or Items</label>
                <input id="search" name="search" class="form-control" value="{{ $search }}" placeholder="Order #, customer, address, or item name">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Search</button>
                <a href="{{ route('checker.spot-checks') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($orders as $order)
        @php
            $mapQuery = $order->delivery_latitude && $order->delivery_longitude
                ? $order->delivery_latitude . ',' . $order->delivery_longitude
                : ($order->delivery_address ? rawurlencode($order->delivery_address) : null);

            $driverName = $order->driver?->name ?? $order->driver_name ?? 'Unassigned';
            $driverPhone = $order->driver?->phone ?? $order->driver_phone;
        @endphp
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $order->order_number }}</h5>
                            <div class="small text-muted">Customer: {{ $order->customer?->name ?? 'Unknown' }}</div>
                            <div class="small text-muted">Scheduled: {{ $order->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</div>
                        </div>
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'primary' }}">{{ ucfirst($order->status) }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="small text-uppercase text-muted mb-1">Destination</div>
                            <div class="mb-3">{{ $order->delivery_address ?: 'Not provided' }}</div>

                            <div class="small text-uppercase text-muted mb-1">Assigned Driver</div>
                            <div>{{ $driverName }}</div>
                            <div class="small text-muted mb-3">{{ $driverPhone ?: 'No contact set' }}</div>

                            <div class="small text-uppercase text-muted mb-1">Items to Verify</div>
                            <ul class="mb-0 ps-3">
                                @forelse($order->items as $item)
                                    <li>{{ $item->product?->name ?? 'Unknown item' }} x {{ $item->quantity }}</li>
                                @empty
                                    <li class="text-muted">No items found</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="col-lg-5">
                            <div class="border rounded p-3 bg-light-subtle h-100">
                                <h6 class="mb-3">Spot Check Checklist</h6>
                                <ul class="mb-3 ps-3">
                                    <li>Driver identity validated</li>
                                    <li>Items match quantity list</li>
                                    <li>Destination/address verified</li>
                                    <li>Proof of delivery capture confirmed</li>
                                </ul>

                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Open Order Details</a>
                                    @if($mapQuery)
                                        <a href="https://www.google.com/maps?q={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">Open Map</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">No orders found for checker spot checks.</div>
        </div>
    @endforelse
</div>

@if($orders->hasPages())
    <div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
