@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Sales Reports</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.excel', ['range' => $range, 'from' => $customFrom, 'to' => $customTo, 'status' => $status, 'payment_method' => $paymentMethod, 'payment_status' => $paymentStatus]) }}" class="btn btn-success">Export Excel (CSV)</a>
        <a href="{{ route('reports.export.pdf', ['range' => $range, 'from' => $customFrom, 'to' => $customTo, 'status' => $status, 'payment_method' => $paymentMethod, 'payment_status' => $paymentStatus]) }}" class="btn btn-danger">Export PDF</a>
    </div>
</div>

<form method="GET" action="{{ route('reports.index') }}" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Range</label>
            <select name="range" class="form-select">
                <option value="daily" @selected($range === 'daily')>Daily</option>
                <option value="weekly" @selected($range === 'weekly')>Weekly</option>
                <option value="monthly" @selected($range === 'monthly')>Monthly</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From Date</label>
            <input type="date" name="from" class="form-control" value="{{ $customFrom ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To Date</label>
            <input type="date" name="to" class="form-control" value="{{ $customTo ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="all" @selected($status === 'all')>All</option>
                <option value="pending" @selected($status === 'pending')>Pending</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
                <option value="delivered" @selected($status === 'delivered')>Delivered</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select">
                <option value="all" @selected($paymentMethod === 'all')>All</option>
                <option value="cash" @selected($paymentMethod === 'cash')>Cash</option>
                <option value="cod" @selected($paymentMethod === 'cod')>COD</option>
                <option value="check" @selected($paymentMethod === 'check')>Check</option>
                <option value="credit_card" @selected($paymentMethod === 'credit_card')>Credit Card</option>
                <option value="gcash" @selected($paymentMethod === 'gcash')>GCash</option>
                <option value="bank_transfer" @selected($paymentMethod === 'bank_transfer')>Bank Transfer</option>
                <option value="other" @selected($paymentMethod === 'other')>Other</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" class="form-select">
                <option value="all" @selected($paymentStatus === 'all')>All</option>
                <option value="unpaid" @selected($paymentStatus === 'unpaid')>Unpaid</option>
                <option value="pending" @selected($paymentStatus === 'pending')>Pending</option>
                <option value="paid" @selected($paymentStatus === 'paid')>Paid</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary w-100">Apply</button>
        </div>
        @if($customFrom || $customTo || $status !== 'all' || $paymentMethod !== 'all' || $paymentStatus !== 'all')
            <div class="col-md-1">
                <a href="{{ route('reports.index') }}" class="btn btn-secondary w-100">Clear</a>
            </div>
        @endif
    </div>
</form>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Period:</strong> {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</p>
        <h5 class="mb-1">Total Sales: ₱{{ number_format($salesTotal, 2) }}</h5>
        <div class="text-muted">Collected Payments: ₱{{ number_format($collectedTotal, 2) }}</div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Reference</th>
                    <th>Paid</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer?->name }}</td>
                        <td>{{ ucfirst($order->status) }}</td>
                        <td>
                            {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'cod')) }}
                            @if($order->payment_method === 'other' && $order->payment_other_method)
                                ({{ $order->payment_other_method }})
                            @endif
                        </td>
                        <td>{{ ucfirst($order->payment_status ?? 'unpaid') }}</td>
                        <td>{{ $order->payment_reference ?: '-' }}</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                ₱{{ number_format((float) ($order->paid_amount ?? $order->total_amount), 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">No sales records for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
