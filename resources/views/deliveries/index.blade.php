@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-1">Delivery Monitoring</h4>
        <p class="text-muted mb-0">Track outgoing orders, open delivery locations, and update delivery status or notes.</p>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">View All Orders</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-warning-subtle shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Pending</div>
                <div class="display-6 fw-semibold">{{ $summary['pending'] }}</div>
                <div class="small text-muted">Waiting for approval or dispatch.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-primary-subtle shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Approved</div>
                <div class="display-6 fw-semibold">{{ $summary['approved'] }}</div>
                <div class="small text-muted">Ready for active delivery handling.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success-subtle shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Delivered</div>
                <div class="display-6 fw-semibold">{{ $summary['delivered'] }}</div>
                <div class="small text-muted">Completed deliveries in the current result set.</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Delivery Calendar</h5>
                <p class="text-muted mb-0">Schedule visibility for pending, approved, and delivered orders.</p>
            </div>
            <div class="small text-muted">Showing up to 300 matching deliveries</div>
        </div>
        <div id="deliveryCalendar"></div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('deliveries.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Order #, customer, or delivery address"
                >
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="delivered" @selected($status === 'delivered')>Delivered</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('deliveries.index') }}" class="btn btn-outline-secondary">Reset</a>
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

            $statusClass = match ($order->status) {
                'pending' => 'warning text-dark',
                'approved' => 'primary',
                'delivered' => 'success',
                default => 'secondary',
            };
        @endphp
        <div class="col-12">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <h5 class="mb-0">{{ $order->order_number }}</h5>
                                <span class="badge bg-{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                            </div>
                            <div class="text-muted small">Customer: {{ $order->customer?->name ?? 'Unknown' }}</div>
                            <div class="text-muted small">Ordered: {{ $order->created_at->format('M d, Y h:i A') }}</div>
                            <div class="text-muted small">Scheduled: {{ $order->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</div>
                            <div class="text-muted small">Items: {{ $order->items_count }}</div>
                        </div>
                        <div class="text-md-end">
                            <div class="fw-semibold">₱{{ number_format($order->total_amount, 2) }}</div>
                            <div class="small text-muted">Last update: {{ $order->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Delivery Address</div>
                                <div>{{ $order->delivery_address ?: 'Not provided' }}</div>
                                @if($order->delivery_latitude && $order->delivery_longitude)
                                    <div class="small text-muted mt-1">
                                        Coordinates: {{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Customer Notes</div>
                                <div>{{ $order->notes ?: 'No customer notes provided.' }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Delivery Notes</div>
                                <div>{{ $order->delivery_notes ?: 'No delivery notes yet.' }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Driver Assignment</div>
                                <div>{{ $order->driver_name ?: 'No driver assigned yet.' }}</div>
                                @if($order->driver_phone)
                                    <div class="small text-muted">Contact: {{ $order->driver_phone }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="small text-uppercase text-muted mb-1">Proof of Delivery</div>
                                @if($order->proof_of_delivery_path)
                                    <a href="{{ $order->proof_of_delivery_path }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success">Open Uploaded Proof</a>
                                    @if($order->delivered_at)
                                        <div class="small text-muted mt-2">Delivered at {{ $order->delivered_at->format('M d, Y h:i A') }}</div>
                                    @endif
                                @else
                                    <div>No proof uploaded yet.</div>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">Open Order</a>
                                @if($mapQuery)
                                    <a href="https://www.google.com/maps?q={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">Open Map</a>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $mapQuery }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">Directions</a>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <form method="POST" action="{{ route('orders.update-status', $order) }}" enctype="multipart/form-data" class="border rounded p-3 bg-light-subtle">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="status-{{ $order->id }}" class="form-label">Delivery Status</label>
                                    <select id="status-{{ $order->id }}" name="status" class="form-select">
                                        <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                        <option value="approved" @selected($order->status === 'approved')>Approved</option>
                                        <option value="delivered" @selected($order->status === 'delivered')>Delivered</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes-{{ $order->id }}" class="form-label">Delivery Notes</label>
                                    <textarea
                                        id="notes-{{ $order->id }}"
                                        name="delivery_notes"
                                        rows="3"
                                        class="form-control"
                                        placeholder="Add delivery remarks, handoff details, or driver updates"
                                    >{{ old('delivery_notes.' . $order->id, $order->delivery_notes) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="scheduled-for-{{ $order->id }}" class="form-label">Scheduled Delivery Date</label>
                                    <input
                                        type="date"
                                        id="scheduled-for-{{ $order->id }}"
                                        name="scheduled_for"
                                        class="form-control"
                                        value="{{ old('scheduled_for', $order->scheduled_for?->toDateString()) }}"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="driver-name-{{ $order->id }}" class="form-label">Assigned Driver</label>
                                    <input
                                        type="text"
                                        id="driver-name-{{ $order->id }}"
                                        name="driver_name"
                                        class="form-control"
                                        value="{{ old('driver_name.' . $order->id, $order->driver_name) }}"
                                        placeholder="Driver name"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="driver-phone-{{ $order->id }}" class="form-label">Driver Contact</label>
                                    <input
                                        type="text"
                                        id="driver-phone-{{ $order->id }}"
                                        name="driver_phone"
                                        class="form-control"
                                        value="{{ old('driver_phone.' . $order->id, $order->driver_phone) }}"
                                        placeholder="Phone number"
                                    >
                                </div>

                                    @if($order->proof_of_delivery_path)
                                        <div class="mb-2">
                                            @php $isPdf = str_ends_with($order->proof_of_delivery_path, '.pdf'); @endphp
                                            @if($isPdf)
                                                <a href="{{ asset($order->proof_of_delivery_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                                                    View Uploaded PDF Receipt
                                                </a>
                                            @else
                                                <a href="{{ asset($order->proof_of_delivery_path) }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ asset($order->proof_of_delivery_path) }}" alt="Proof of Delivery" class="img-fluid rounded border" style="max-height: 120px; object-fit: contain;">
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                <button type="submit" class="btn btn-success w-100">Save Delivery Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    No deliveries matched the current filters.
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
        #deliveryCalendar {
            min-height: 600px;
        }

        #deliveryCalendar .fc .fc-daygrid-day-number {
            color: #1f2937;
            text-decoration: none;
        }

        #deliveryCalendar .delivery-status-pending {
            background-color: #f59e0b;
            border-color: #d97706;
        }

        #deliveryCalendar .delivery-status-approved {
            background-color: #2563eb;
            border-color: #1d4ed8;
        }

        #deliveryCalendar .delivery-status-delivered {
            background-color: #16a34a;
            border-color: #15803d;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarElement = document.getElementById('deliveryCalendar');

            if (!calendarElement || typeof FullCalendar === 'undefined') {
                return;
            }

            const events = @json($calendarEvents);
            const calendar = new FullCalendar.Calendar(calendarElement, {
                initialView: 'dayGridMonth',
                height: 'auto',
                events,
                eventDisplay: 'block',
                dayMaxEvents: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek',
                },
                eventDidMount(info) {
                    const total = info.event.extendedProps.total;
                    const status = info.event.extendedProps.status;
                    info.el.title = `${info.event.title}\nStatus: ${status}\nTotal: PHP ${total}`;
                },
            });

            calendar.render();
        });
    </script>
@endpush