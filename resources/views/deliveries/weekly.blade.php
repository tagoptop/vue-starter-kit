@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Weekly Delivery Schedule</h4>
        <p class="text-muted mb-0">Comprehensive weekly dispatch view for deliveries, assigned drivers, and delivery items.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('deliveries.index') }}" class="btn btn-outline-secondary">Back to Delivery Monitoring</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="text-muted small">Current Week</div>
            <div class="fw-semibold">{{ $weekStart->format('M d, Y') }} - {{ $weekEnd->format('M d, Y') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('deliveries.weekly', ['week_start' => $previousWeekStart]) }}" class="btn btn-outline-primary">Previous Week</a>
            <a href="{{ route('deliveries.weekly') }}" class="btn btn-outline-dark">This Week</a>
            <a href="{{ route('deliveries.weekly', ['week_start' => $nextWeekStart]) }}" class="btn btn-outline-primary">Next Week</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-primary-subtle">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Scheduled Orders</div>
                <div class="display-6 fw-semibold">{{ $summary['totalOrders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-info-subtle">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Total Items</div>
                <div class="display-6 fw-semibold">{{ $summary['totalItems'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-warning-subtle">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Orders With Driver</div>
                <div class="display-6 fw-semibold">{{ $summary['assignedDrivers'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100 border-success-subtle">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Delivered This Week</div>
                <div class="display-6 fw-semibold">{{ $summary['deliveredOrders'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Driver Workload</h5>
            <span class="small text-muted">Based on scheduled deliveries this week</span>
        </div>

        @if($driverWorkloads->isEmpty())
            <p class="text-muted mb-0">No scheduled deliveries for this week yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th class="text-end">Orders</th>
                            <th class="text-end">Items</th>
                            <th class="text-end">Delivery Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driverWorkloads as $workload)
                            <tr>
                                <td>{{ $workload['driver'] }}</td>
                                <td class="text-end">{{ $workload['orders'] }}</td>
                                <td class="text-end">{{ $workload['items'] }}</td>
                                <td class="text-end">₱{{ number_format((float) $workload['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="row g-4">
    @foreach($dailySchedules as $daySchedule)
        @php
            $date = $daySchedule['date'];
            $orders = $daySchedule['orders'];
        @endphp

        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">{{ $date->format('l') }}</h5>
                        <div class="small text-muted">{{ $date->format('M d, Y') }}</div>
                    </div>
                    <div class="d-flex gap-3 small">
                        <span><strong>{{ $daySchedule['totalOrders'] }}</strong> delivery(ies)</span>
                        <span><strong>{{ $daySchedule['totalItems'] }}</strong> item(s)</span>
                    </div>
                </div>

                <div class="card-body">
                    @if($orders->isEmpty())
                        <p class="text-muted mb-0">No deliveries scheduled.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Driver</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Items to Deliver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        @php
                                            $statusClass = match ($order->status) {
                                                'pending' => 'warning text-dark',
                                                'approved' => 'primary',
                                                'delivered' => 'success',
                                                default => 'secondary',
                                            };

                                            $driverLabel = $order->driver?->name
                                                ?? $order->driver_name
                                                ?? 'Unassigned';
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->order_number }}</a>
                                                <div class="small text-muted">₱{{ number_format((float) $order->total_amount, 2) }}</div>
                                            </td>
                                            <td>{{ $order->customer?->name ?? 'Unknown customer' }}</td>
                                            <td>
                                                <div>{{ $driverLabel }}</div>
                                                @if($order->driver?->phone || $order->driver_phone)
                                                    <div class="small text-muted">{{ $order->driver?->phone ?? $order->driver_phone }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $order->delivery_address ?: 'Not provided' }}</td>
                                            <td><span class="badge bg-{{ $statusClass }}">{{ ucfirst($order->status) }}</span></td>
                                            <td>
                                                @if($order->items->isEmpty())
                                                    <span class="text-muted">No items</span>
                                                @else
                                                    <ul class="mb-0 ps-3">
                                                        @foreach($order->items as $item)
                                                            <li>{{ $item->product?->name ?? 'Unknown item' }} x {{ $item->quantity }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Unscheduled Pending and Approved Deliveries</h5>
            <span class="small text-muted">Needs schedule assignment</span>
        </div>

        @if($unscheduledOrders->isEmpty())
            <p class="text-muted mb-0">No unscheduled pending or approved deliveries.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unscheduledOrders as $order)
                            @php
                                $driverLabel = $order->driver?->name
                                    ?? $order->driver_name
                                    ?? 'Unassigned';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="text-decoration-none">{{ $order->order_number }}</a>
                                </td>
                                <td>{{ $order->customer?->name ?? 'Unknown customer' }}</td>
                                <td>{{ $driverLabel }}</td>
                                <td>{{ ucfirst($order->status) }}</td>
                                <td>{{ $order->items->sum('quantity') }}</td>
                                <td class="text-end">₱{{ number_format((float) $order->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
