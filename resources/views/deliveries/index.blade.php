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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Filtered Delivery Cards</h5>
                <p class="text-muted mb-0">Swipe left or right to browse columns, then drag a card to update delivery status.</p>
            </div>
            <div class="small text-muted">Showing {{ $orders->count() }} cards on this page</div>
        </div>

        <div id="deliveryBoardAlert" class="alert alert-success py-2 px-3 d-none" role="status" aria-live="polite"></div>

        <div class="delivery-board" id="deliveryBoard" aria-label="Delivery status board">
            @php
                $boardGroups = [
                    'pending' => ['label' => 'Pending', 'badge' => 'warning text-dark'],
                    'approved' => ['label' => 'Approved', 'badge' => 'primary'],
                    'delivered' => ['label' => 'Delivered', 'badge' => 'success'],
                ];
            @endphp

            @foreach($boardGroups as $boardStatus => $boardMeta)
                @php
                    $statusOrders = $orders->where('status', $boardStatus)->values();
                @endphp
                <section class="delivery-lane" data-status="{{ $boardStatus }}" aria-label="{{ $boardMeta['label'] }} deliveries">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">{{ $boardMeta['label'] }}</h6>
                        <span class="badge bg-{{ $boardMeta['badge'] }}" data-lane-count>{{ $statusOrders->count() }}</span>
                    </div>

                    <div class="delivery-lane-dropzone" data-dropzone>
                        @forelse($statusOrders as $boardOrder)
                            <article
                                class="delivery-swipe-card"
                                draggable="true"
                                data-card-id="{{ $boardOrder->id }}"
                                data-order-number="{{ $boardOrder->order_number }}"
                                data-current-status="{{ $boardOrder->status }}"
                                data-customer-name="{{ $boardOrder->customer?->name ?? 'Unknown' }}"
                                data-address="{{ $boardOrder->delivery_address ?: 'Not provided' }}"
                                data-total="{{ number_format((float) $boardOrder->total_amount, 2) }}"
                                data-items-count="{{ $boardOrder->items_count }}"
                                data-scheduled-for="{{ $boardOrder->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}"
                                data-scheduled-for-value="{{ $boardOrder->scheduled_for?->toDateString() ?? '' }}"
                                data-driver-name="{{ $boardOrder->driver_name ?? '' }}"
                                data-driver-phone="{{ $boardOrder->driver_phone ?? '' }}"
                                data-delivery-notes="{{ $boardOrder->delivery_notes ?? '' }}"
                            >
                                <div class="d-flex justify-content-between gap-2 mb-2">
                                    <strong>{{ $boardOrder->order_number }}</strong>
                                    <span class="badge bg-light text-dark border">{{ ucfirst($boardOrder->status) }}</span>
                                </div>
                                <div class="small text-muted mb-1">Name</div>
                                <div class="mb-2 fw-semibold">{{ $boardOrder->customer?->name ?? 'Unknown' }}</div>
                                <div class="small text-muted mb-1">Address</div>
                                <div class="mb-2">{{ $boardOrder->delivery_address ?: 'Not provided' }}</div>
                                <div class="small text-muted mb-1">Order Details</div>
                                <div class="small">Items: {{ $boardOrder->items_count }} | Total: PHP {{ number_format((float) $boardOrder->total_amount, 2) }}</div>
                                <div class="small text-muted">Scheduled: {{ $boardOrder->scheduled_for?->format('M d, Y') ?? 'Not scheduled' }}</div>
                            </article>
                        @empty
                            <div class="delivery-lane-empty text-muted" data-lane-empty>
                                No {{ strtolower($boardMeta['label']) }} cards.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
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

        .delivery-board {
            display: grid;
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            touch-action: pan-x;
            scroll-snap-type: x mandatory;
        }

        .delivery-lane {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem;
            min-height: 280px;
            scroll-snap-align: start;
        }

        .delivery-lane-dropzone {
            min-height: 220px;
            display: grid;
            gap: 0.6rem;
            align-content: start;
        }

        .delivery-lane.is-drop-target {
            border-color: #2563eb;
            box-shadow: inset 0 0 0 1px #2563eb;
            background: #eff6ff;
        }

        .delivery-swipe-card {
            border: 1px solid #dbeafe;
            border-radius: 0.65rem;
            padding: 0.75rem;
            background: #ffffff;
            cursor: grab;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .delivery-swipe-card.is-dragging {
            opacity: 0.7;
            cursor: grabbing;
            transform: scale(0.99);
        }

        .delivery-swipe-card:hover {
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .delivery-lane-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            background: #f8fafc;
        }

        @media (max-width: 992px) {
            .delivery-board {
                grid-template-columns: repeat(3, minmax(290px, 82vw));
            }
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

            const board = document.getElementById('deliveryBoard');
            if (!board) {
                return;
            }

            const csrfToken = @json(csrf_token());
            const updateTemplate = @json(route('orders.update-status', '__ORDER__'));
            const reorderUrl = @json(route('deliveries.reorder'));
            const alertElement = document.getElementById('deliveryBoardAlert');
            let draggedCard = null;
            let swipePointer = null;

            const showBoardAlert = (message, isError = false) => {
                if (!alertElement) {
                    return;
                }

                alertElement.classList.remove('d-none', 'alert-success', 'alert-danger');
                alertElement.classList.add(isError ? 'alert-danger' : 'alert-success');
                alertElement.textContent = message;
            };

            const refreshLaneEmptyState = (lane) => {
                const dropzone = lane.querySelector('[data-dropzone]');
                if (!dropzone) {
                    return;
                }

                const hasCards = dropzone.querySelector('.delivery-swipe-card') !== null;
                let emptyState = dropzone.querySelector('[data-lane-empty]');

                if (!hasCards && !emptyState) {
                    emptyState = document.createElement('div');
                    emptyState.className = 'delivery-lane-empty text-muted';
                    emptyState.setAttribute('data-lane-empty', 'true');
                    emptyState.textContent = `No ${lane.dataset.status} cards.`;
                    dropzone.appendChild(emptyState);
                }

                if (hasCards && emptyState) {
                    emptyState.remove();
                }
            };

            const refreshLaneCounts = () => {
                board.querySelectorAll('.delivery-lane').forEach((lane) => {
                    const countTag = lane.querySelector('[data-lane-count]');
                    if (!countTag) {
                        return;
                    }
                    const count = lane.querySelectorAll('.delivery-swipe-card').length;
                    countTag.textContent = String(count);
                    refreshLaneEmptyState(lane);
                });
            };

            const updateCardStatus = (card, laneStatus) => {
                const badge = card.querySelector('.badge');
                if (badge) {
                    badge.textContent = laneStatus.charAt(0).toUpperCase() + laneStatus.slice(1);
                }
                card.dataset.currentStatus = laneStatus;
            };

            const cardIdsInLane = (lane) => {
                const dropzone = lane.querySelector('[data-dropzone]');
                if (!dropzone) {
                    return [];
                }

                return Array.from(dropzone.querySelectorAll('.delivery-swipe-card'))
                    .map((card) => Number(card.dataset.cardId))
                    .filter((id) => Number.isInteger(id) && id > 0);
            };

            const persistLaneOrder = async (lane) => {
                const laneStatus = lane.dataset.status;
                const orderedIds = cardIdsInLane(lane);

                if (!laneStatus || orderedIds.length === 0) {
                    return { ok: true };
                }

                const response = await fetch(reorderUrl, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: laneStatus,
                        ordered_ids: orderedIds,
                    }),
                });

                if (!response.ok) {
                    return { ok: false, message: 'Unable to save lane order. Please retry.' };
                }

                return { ok: true };
            };

            const getDragAfterElement = (container, y) => {
                const candidates = [...container.querySelectorAll('.delivery-swipe-card:not(.is-dragging)')];

                return candidates.reduce((closest, card) => {
                    const box = card.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;

                    if (offset < 0 && offset > closest.offset) {
                        return { offset, element: card };
                    }

                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
            };

            const persistStatusChange = async (card, newStatus) => {
                const orderId = card.dataset.cardId;
                if (!orderId) {
                    return { ok: false, message: 'Missing order id.' };
                }

                const formData = new FormData();
                formData.append('_method', 'PATCH');
                formData.append('status', newStatus);
                formData.append('scheduled_for', card.dataset.scheduledForValue || '');
                formData.append('driver_name', card.dataset.driverName || '');
                formData.append('driver_phone', card.dataset.driverPhone || '');
                formData.append('delivery_notes', card.dataset.deliveryNotes || '');

                const response = await fetch(updateTemplate.replace('__ORDER__', orderId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    return { ok: false, message: 'Unable to save status. Please try again.' };
                }

                return { ok: true };
            };

            board.querySelectorAll('.delivery-swipe-card').forEach((card) => {
                card.addEventListener('dragstart', (event) => {
                    draggedCard = card;
                    card.classList.add('is-dragging');
                    event.dataTransfer?.setData('text/plain', card.dataset.cardId || '');
                    event.dataTransfer.effectAllowed = 'move';
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('is-dragging');
                    board.querySelectorAll('.delivery-lane').forEach((lane) => lane.classList.remove('is-drop-target'));
                    draggedCard = null;
                });
            });

            board.querySelectorAll('.delivery-lane').forEach((lane) => {
                lane.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    lane.classList.add('is-drop-target');

                    if (!draggedCard) {
                        return;
                    }

                    const dropzone = lane.querySelector('[data-dropzone]');
                    if (!dropzone) {
                        return;
                    }

                    const afterElement = getDragAfterElement(dropzone, event.clientY);
                    if (!afterElement) {
                        dropzone.appendChild(draggedCard);
                        return;
                    }

                    dropzone.insertBefore(draggedCard, afterElement);
                });

                lane.addEventListener('dragleave', () => {
                    lane.classList.remove('is-drop-target');
                });

                lane.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    lane.classList.remove('is-drop-target');

                    if (!draggedCard) {
                        return;
                    }

                    const newStatus = lane.dataset.status;
                    const previousLane = draggedCard.closest('.delivery-lane');
                    const previousStatus = draggedCard.dataset.currentStatus;
                    const dropzone = lane.querySelector('[data-dropzone]');

                    if (!newStatus || !dropzone || !previousLane) {
                        return;
                    }

                    const previousLaneState = cardIdsInLane(previousLane);
                    const currentLaneState = cardIdsInLane(lane);

                    if (newStatus === previousStatus) {
                        refreshLaneCounts();

                        const reorderResult = await persistLaneOrder(lane);
                        if (!reorderResult.ok) {
                            showBoardAlert(reorderResult.message || 'Unable to save card order.', true);
                        } else {
                            showBoardAlert(`Saved order for ${newStatus} lane.`);
                        }

                        return;
                    }

                    updateCardStatus(draggedCard, newStatus);
                    refreshLaneCounts();

                    try {
                        const result = await persistStatusChange(draggedCard, newStatus);
                        if (!result.ok) {
                            const previousDropzone = previousLane.querySelector('[data-dropzone]');
                            if (previousDropzone) {
                                const snapshotCards = previousLaneState
                                    .map((id) => board.querySelector(`.delivery-swipe-card[data-card-id="${id}"]`))
                                    .filter(Boolean);

                                snapshotCards.forEach((snapshotCard) => previousDropzone.appendChild(snapshotCard));
                                updateCardStatus(draggedCard, previousStatus || 'pending');
                                refreshLaneCounts();
                            }

                            showBoardAlert(result.message || 'Unable to move card.', true);
                            return;
                        }

                        const [targetLanePersist, previousLanePersist] = await Promise.all([
                            persistLaneOrder(lane),
                            previousLane === lane ? Promise.resolve({ ok: true }) : persistLaneOrder(previousLane),
                        ]);

                        if (!targetLanePersist.ok || !previousLanePersist.ok) {
                            const previousDropzone = previousLane.querySelector('[data-dropzone]');
                            if (previousDropzone) {
                                const snapshotCards = previousLaneState
                                    .map((id) => board.querySelector(`.delivery-swipe-card[data-card-id="${id}"]`))
                                    .filter(Boolean);
                                snapshotCards.forEach((snapshotCard) => previousDropzone.appendChild(snapshotCard));

                                const newLaneDropzone = lane.querySelector('[data-dropzone]');
                                if (newLaneDropzone) {
                                    const currentCards = currentLaneState
                                        .map((id) => board.querySelector(`.delivery-swipe-card[data-card-id="${id}"]`))
                                        .filter(Boolean);
                                    currentCards.forEach((currentCard) => newLaneDropzone.appendChild(currentCard));
                                }

                                updateCardStatus(draggedCard, previousStatus || 'pending');
                                refreshLaneCounts();
                            }

                            showBoardAlert('Unable to persist lane priorities after status change.', true);
                            return;
                        }

                        showBoardAlert(`Updated ${draggedCard.dataset.orderNumber} to ${newStatus}.`);
                    } catch (error) {
                        const previousDropzone = previousLane.querySelector('[data-dropzone]');
                        if (previousDropzone) {
                            const snapshotCards = previousLaneState
                                .map((id) => board.querySelector(`.delivery-swipe-card[data-card-id="${id}"]`))
                                .filter(Boolean);
                            snapshotCards.forEach((snapshotCard) => previousDropzone.appendChild(snapshotCard));
                            updateCardStatus(draggedCard, previousStatus || 'pending');
                            refreshLaneCounts();
                        }

                        showBoardAlert('Network error while saving card movement.', true);
                    }
                });
            });

            board.addEventListener('pointerdown', (event) => {
                if (!event.isPrimary || event.pointerType === 'mouse') {
                    return;
                }

                if (event.target.closest('.delivery-swipe-card')) {
                    return;
                }

                swipePointer = {
                    startX: event.clientX,
                    startScrollLeft: board.scrollLeft,
                    pointerId: event.pointerId,
                };
                board.setPointerCapture(event.pointerId);
            });

            board.addEventListener('pointermove', (event) => {
                if (!swipePointer || swipePointer.pointerId !== event.pointerId) {
                    return;
                }

                const delta = event.clientX - swipePointer.startX;
                board.scrollLeft = swipePointer.startScrollLeft - delta;
            });

            const clearSwipePointer = () => {
                swipePointer = null;
            };

            board.addEventListener('pointerup', clearSwipePointer);
            board.addEventListener('pointercancel', clearSwipePointer);

            refreshLaneCounts();
        });
    </script>
@endpush