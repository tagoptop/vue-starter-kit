@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Orders</h4>
    @if(auth()->user()->role === 'customer')
        <a href="{{ route('orders.create') }}" class="btn btn-primary">Place Order</a>
    @endif
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th width="250">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $mapQuery = $order->delivery_latitude && $order->delivery_longitude
                            ? $order->delivery_latitude . ',' . $order->delivery_longitude
                            : ($order->delivery_address ? rawurlencode($order->delivery_address) : null);
                    @endphp
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-info">View</a>
                            @if(in_array(auth()->user()->role, ['admin', 'staff']) && $mapQuery)
                                <a href="https://www.google.com/maps?q={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">Map</a>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'staff']))
                                <form action="{{ route('orders.update-status', $order) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                        <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                        <option value="approved" @selected($order->status === 'approved')>Approved</option>
                                        <option value="delivered" @selected($order->status === 'delivered')>Delivered</option>
                                    </select>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $orders->links() }}</div>
@endsection
