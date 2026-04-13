@extends('layouts.app')

@section('content')
@php
    $mapQuery = $order->delivery_latitude && $order->delivery_longitude
        ? $order->delivery_latitude . ',' . $order->delivery_longitude
        : ($order->delivery_address ? rawurlencode($order->delivery_address) : null);
    $directionsDestination = $order->delivery_latitude && $order->delivery_longitude
        ? $order->delivery_latitude . ',' . $order->delivery_longitude
        : ($order->delivery_address ? rawurlencode($order->delivery_address) : null);
@endphp
<h4 class="mb-3">Order Details</h4>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p class="mb-1"><strong>Customer:</strong> {{ $order->customer?->name }}</p>
        <p class="mb-1"><strong>Delivery Address:</strong> {{ $order->delivery_address ?: 'Not provided' }}</p>
        @if($order->delivery_latitude && $order->delivery_longitude)
            <p class="mb-1">
                <strong>Location Pin:</strong>
                <a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}" target="_blank" rel="noopener noreferrer">
                    View map marker
                </a>
                <span class="text-muted small">({{ $order->delivery_latitude }}, {{ $order->delivery_longitude }})</span>
            </p>
        @endif
        @if($mapQuery)
            <p class="mb-1">
                <strong>Map:</strong>
                <a href="https://www.google.com/maps?q={{ $mapQuery }}" target="_blank" rel="noopener noreferrer">Open delivery location</a>
            </p>
        @endif
        <p class="mb-1"><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        <p class="mb-1"><strong>Total:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>
        <p class="mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>
    </div>
</div>

@if(in_array(auth()->user()->role, ['admin', 'staff']) && $directionsDestination)
    <div class="card shadow-sm mb-3 border-primary-subtle">
        <div class="card-body">
            <h5 class="card-title mb-2">Delivery Navigation</h5>
            <p class="text-muted mb-3">Use the saved delivery location to help delivery personnel open directions from their current location.</p>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button
                    type="button"
                    class="btn btn-primary"
                    id="navigateDeliveryBtn"
                    data-destination="{{ $directionsDestination }}"
                >
                    Navigate from Current Location
                </button>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $directionsDestination }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                    Open Directions
                </a>
            </div>
            <div id="deliveryNavigationStatus" class="small text-muted mt-2"></div>
        </div>
    </div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@if(in_array(auth()->user()->role, ['admin', 'staff']) && $directionsDestination)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navigateButton = document.getElementById('navigateDeliveryBtn');
            const status = document.getElementById('deliveryNavigationStatus');

            navigateButton?.addEventListener('click', function () {
                if (! navigator.geolocation) {
                    status.textContent = 'Geolocation is not supported by this browser.';
                    return;
                }

                status.textContent = 'Getting current location for navigation...';

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const origin = `${position.coords.latitude.toFixed(7)},${position.coords.longitude.toFixed(7)}`;
                        const destination = navigateButton.dataset.destination;

                        window.open(
                            `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}`,
                            '_blank',
                            'noopener,noreferrer'
                        );

                        status.textContent = 'Directions opened in Google Maps.';
                    },
                    function () {
                        status.textContent = 'Unable to get current location. Please allow location access and try again.';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                    }
                );
            });
        });
    </script>
    @endpush
@endif
