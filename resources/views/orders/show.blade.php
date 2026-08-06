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
        <p class="mb-1"><strong>Scheduled Delivery:</strong> {{ $order->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</p>
        <p class="mb-1">
            <strong>Transaction Method:</strong>
            {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'cod')) }}
            @if($order->payment_method === 'other' && $order->payment_other_method)
                ({{ $order->payment_other_method }})
            @endif
        </p>
        <p class="mb-1"><strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'unpaid') }}</p>
        @if($order->payment_reference)
            <p class="mb-1"><strong>Payment Reference:</strong> {{ $order->payment_reference }}</p>
        @endif
        @if($order->paid_amount)
            <p class="mb-1"><strong>Amount Paid:</strong> ₱{{ number_format($order->paid_amount, 2) }}</p>
        @endif
        @if($order->paid_at)
            <p class="mb-1"><strong>Paid At:</strong> {{ $order->paid_at->format('M d, Y h:i A') }}</p>
        @endif
        @if($order->payment_notes)
            <p class="mb-1"><strong>Payment Notes:</strong> {{ $order->payment_notes }}</p>
        @endif
        <p class="mb-1"><strong>Total:</strong> ₱{{ number_format($order->total_amount, 2) }}</p>
        <p class="mb-1"><strong>Customer Notes:</strong> {{ $order->notes ?: 'No customer notes provided.' }}</p>
        @if($order->proof_of_delivery_path)
            <div class="mt-3">
                <strong>Proof of Delivery:</strong>
                @php $isPdf = str_ends_with($order->proof_of_delivery_path, '.pdf'); @endphp
                @if($isPdf)
                    <div class="mt-2">
                        <a href="{{ asset($order->proof_of_delivery_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                            View PDF Receipt
                        </a>
                    </div>
                @else
                    <div class="mt-2">
                        <a href="{{ asset($order->proof_of_delivery_path) }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ asset($order->proof_of_delivery_path) }}" alt="Proof of Delivery" class="img-fluid rounded border" style="max-height: 200px; object-fit: contain;">
                        </a>
                    </div>
                @endif
            </div>
        @endif

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
