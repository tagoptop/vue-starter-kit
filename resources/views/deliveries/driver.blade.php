@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-1">My Deliveries</h4>
        <p class="text-muted mb-0">Orders ready for delivery with item details and destination addresses.</p>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('driver.deliveries.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-lg-9">
                <label for="search" class="form-label mb-1">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Order #, customer, address, or item"
                >
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">Apply</button>
                <a href="{{ route('driver.deliveries.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items To Deliver</th>
                    <th>Delivery Address</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th width="220">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $delivery)
                    @php
                        $mapQuery = $delivery->delivery_latitude && $delivery->delivery_longitude
                            ? $delivery->delivery_latitude . ',' . $delivery->delivery_longitude
                            : ($delivery->delivery_address ? rawurlencode($delivery->delivery_address) : null);
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $delivery->order_number }}</td>
                        <td>{{ $delivery->customer?->name ?? 'Unknown customer' }}</td>
                        <td>
                            @if($delivery->items->isNotEmpty())
                                <ul class="mb-0 ps-3">
                                    @foreach($delivery->items as $item)
                                        <li>{{ $item->product?->name ?? 'Deleted product' }} x {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No items found.</span>
                            @endif
                        </td>
                        <td>{{ $delivery->delivery_address ?: 'Not provided' }}</td>
                        <td>{{ $delivery->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</td>
                        <td>
                            <span class="badge {{ $delivery->status === 'approved' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($delivery->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('orders.show', $delivery) }}" class="btn btn-sm btn-info">View</a>
                            @if($mapQuery)
                                <a
                                    href="https://www.google.com/maps?q={{ $mapQuery }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Map
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No deliveries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $deliveries->links() }}</div>
@endsection
