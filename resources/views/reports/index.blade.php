@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Sales Reports</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export.excel', ['range' => $range]) }}" class="btn btn-success">Export Excel (CSV)</a>
        <a href="{{ route('reports.export.pdf', ['range' => $range]) }}" class="btn btn-danger">Export PDF</a>
    </div>
</div>

<form method="GET" action="{{ route('reports.index') }}" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Range</label>
            <select name="range" class="form-select">
                <option value="daily" @selected($range === 'daily')>Daily</option>
                <option value="weekly" @selected($range === 'weekly')>Weekly</option>
                <option value="monthly" @selected($range === 'monthly')>Monthly</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary">Apply</button>
        </div>
    </div>
</form>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>Period:</strong> {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</p>
        <h5 class="mb-0">Total Sales: ₱{{ number_format($salesTotal, 2) }}</h5>
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
                        <td>₱{{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No sales records for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
