@extends('layouts.app')

@section('content')
@php
    $groupedProducts = $products->groupBy(fn ($product) => $product->category?->name ?? 'Uncategorized');
    $groupedCart = collect($cart)->groupBy(fn ($item) => $item['category'] ?: 'Uncategorized');
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-1">Quick Order Cart</h3>
        <p class="text-muted mb-0">Pick materials, adjust quantities, and check out from the side cart.</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-dark fs-6">{{ $cartCount }} item(s)</span>
        <span class="badge bg-success fs-6">₱{{ number_format($cartTotal, 2) }}</span>
    </div>
</div>

<div class="row g-4 align-items-start">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <ul class="nav nav-pills flex-wrap gap-2" id="categoryTabs" role="tablist">
                    @foreach($groupedProducts as $categoryName => $categoryProducts)
                        @php $slug = Illuminate\Support\Str::slug($categoryName); @endphp
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="tab-{{ $slug }}"
                                data-bs-toggle="pill"
                                data-bs-target="#pane-{{ $slug }}"
                                type="button"
                                role="tab"
                                aria-controls="pane-{{ $slug }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            >
                                {{ $categoryName }}
                                <span class="badge bg-dark ms-1">{{ $categoryProducts->count() }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="tab-content" id="categoryTabsContent">
            @foreach($groupedProducts as $categoryName => $categoryProducts)
                @php $slug = Illuminate\Support\Str::slug($categoryName); @endphp
                <div
                    class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                    id="pane-{{ $slug }}"
                    role="tabpanel"
                    aria-labelledby="tab-{{ $slug }}"
                    tabindex="0"
                >
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">{{ $categoryName }}</h4>
                        <span class="badge bg-secondary">{{ $categoryProducts->count() }} item(s)</span>
                    </div>

                    <div class="row g-3">
                        @foreach($categoryProducts as $product)
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 shadow-sm border-0">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
                                    @else
                                        <div class="bg-warning-subtle d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <span class="fw-bold text-secondary">{{ $categoryName }}</span>
                                        </div>
                                    @endif

                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                            <h5 class="card-title mb-0">{{ $product->name }}</h5>
                                            <span class="badge text-bg-light">{{ $categoryName }}</span>
                                        </div>

                                        <p class="text-muted small mb-2">{{ $product->description ?: 'Construction supply item ready for ordering.' }}</p>
                                        <p class="small mb-2">Supplier: <strong>{{ $product->supplier?->name ?? 'N/A' }}</strong></p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto mb-3">
                                            <span class="fw-bold text-success fs-5">₱{{ number_format($product->price, 2) }}</span>
                                            <span class="small {{ $product->stock_quantity <= $product->low_stock_threshold ? 'text-danger' : 'text-muted' }}">
                                                Stock: {{ $product->stock_quantity }}
                                            </span>
                                        </div>

                                        <form method="POST" action="{{ route('orders.cart.add') }}" class="row g-2 align-items-center">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <div class="col-5">
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    min="1"
                                                    max="{{ $product->stock_quantity }}"
                                                    value="1"
                                                    class="form-control"
                                                    {{ $product->stock_quantity === 0 ? 'disabled' : '' }}
                                                >
                                            </div>
                                            <div class="col-7 d-grid">
                                                <button type="submit" class="btn btn-warning fw-semibold" {{ $product->stock_quantity === 0 ? 'disabled' : '' }}>
                                                    {{ $product->stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 position-sticky" style="top: 1rem;">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Your Cart</span>
                @if($cartCount > 0)
                    <form method="POST" action="{{ route('orders.cart.clear') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">Clear</button>
                    </form>
                @endif
            </div>
            <div class="card-body">
                @if(empty($cart))
                    <div class="text-center py-4 text-muted">
                        <div class="fs-1">🛒</div>
                        <p class="mb-0">Your cart is empty. Add materials to start an order.</p>
                    </div>
                @else
                    <div class="d-flex flex-column gap-3 mb-3">
                        @foreach($groupedCart as $categoryName => $cartItems)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $categoryName }}</h6>
                                    <span class="badge bg-light text-dark">{{ $cartItems->sum('quantity') }} qty</span>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    @foreach($cartItems as $item)
                                        <div class="border rounded p-3">
                                            <div class="d-flex justify-content-between gap-2 mb-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                                    <div class="small text-muted">{{ $categoryName }}</div>
                                                </div>
                                                <form method="POST" action="{{ route('orders.cart.remove', $item['product_id']) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">×</button>
                                                </form>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted small">₱{{ number_format($item['price'], 2) }} each</span>
                                                <span class="fw-semibold">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                            </div>

                                            <form method="POST" action="{{ route('orders.cart.update', $item['product_id']) }}" class="d-flex gap-2 align-items-center">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" min="0" max="{{ $item['stock_quantity'] }}" value="{{ $item['quantity'] }}" class="form-control form-control-sm">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">Update</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-top pt-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items</span>
                            <strong>{{ $cartCount }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong class="text-success fs-5">₱{{ number_format($cartTotal, 2) }}</strong>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('orders.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <textarea
                                name="delivery_address"
                                class="form-control"
                                rows="3"
                                placeholder="Enter the delivery address for this order"
                                required
                            >{{ old('delivery_address', auth()->user()->address) }}</textarea>
                            <div class="form-text">You can change this address every time you place a new order.</div>
                        </div>

                        <div class="mb-3 border rounded p-3 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
                                <div>
                                    <div class="fw-semibold">Location Pin</div>
                                    <div class="small text-muted">Optional: use device GPS or manually set your desired pin location.</div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="useCurrentLocationBtn">Use Current Location</button>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label form-label-sm mb-1" for="delivery_latitude">Latitude</label>
                                    <input
                                        type="number"
                                        step="0.0000001"
                                        min="-90"
                                        max="90"
                                        name="delivery_latitude"
                                        id="delivery_latitude"
                                        class="form-control form-control-sm"
                                        placeholder="e.g. 14.599512"
                                        value="{{ old('delivery_latitude') }}"
                                    >
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label form-label-sm mb-1" for="delivery_longitude">Longitude</label>
                                    <input
                                        type="number"
                                        step="0.0000001"
                                        min="-180"
                                        max="180"
                                        name="delivery_longitude"
                                        id="delivery_longitude"
                                        class="form-control form-control-sm"
                                        placeholder="e.g. 120.984222"
                                        value="{{ old('delivery_longitude') }}"
                                    >
                                </div>
                            </div>

                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="applyManualPinBtn">Apply Manual Pin</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="clearPinBtn">Clear Pin</button>
                            </div>

                            <div id="locationStatus" class="small text-muted">
                                @if(old('delivery_latitude') && old('delivery_longitude'))
                                    Location pin ready: {{ old('delivery_latitude') }}, {{ old('delivery_longitude') }}
                                @else
                                    No location pin added yet.
                                @endif
                            </div>

                            <a
                                href="#"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-link btn-sm px-0 mt-2 {{ old('delivery_latitude') && old('delivery_longitude') ? '' : 'd-none' }}"
                                id="previewMapLink"
                            >
                                Preview map pin
                            </a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="scheduled_for">Requested Delivery Date</label>
                            <input
                                type="date"
                                id="scheduled_for"
                                name="scheduled_for"
                                class="form-control"
                                value="{{ old('scheduled_for') }}"
                                min="{{ now()->toDateString() }}"
                            >
                            <div class="form-text">Optional: this date appears in the delivery calendar for scheduling.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="payment_method">Transaction Method</label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="cod" @selected(old('payment_method', 'cod') === 'cod')>Cash on Delivery (COD)</option>
                                <option value="check" @selected(old('payment_method') === 'check')>Check</option>
                                <option value="credit_card" @selected(old('payment_method') === 'credit_card')>Credit Card</option>
                                <option value="gcash" @selected(old('payment_method') === 'gcash')>GCash</option>
                                <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                                <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="payment_other_method">Other Transaction Method (if Other)</label>
                            <input
                                type="text"
                                id="payment_other_method"
                                name="payment_other_method"
                                class="form-control"
                                value="{{ old('payment_other_method') }}"
                                maxlength="80"
                                placeholder="Specify method, e.g. Maya, PayPal, company charge account"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="payment_reference">Payment Reference (optional)</label>
                            <input
                                type="text"
                                id="payment_reference"
                                name="payment_reference"
                                class="form-control"
                                value="{{ old('payment_reference') }}"
                                maxlength="120"
                                placeholder="Reference number, check number, or transaction id"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="payment_notes">Payment Notes (optional)</label>
                            <textarea id="payment_notes" name="payment_notes" class="form-control" rows="2" placeholder="Any remarks for this transaction">{{ old('payment_notes') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Customer Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Requests, site reminders, or project reference">{{ old('notes') }}</textarea>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-success btn-lg fw-semibold" type="submit">Place Order</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const locationButton = document.getElementById('useCurrentLocationBtn');
        const latitudeInput = document.getElementById('delivery_latitude');
        const longitudeInput = document.getElementById('delivery_longitude');
        const locationStatus = document.getElementById('locationStatus');
        const previewMapLink = document.getElementById('previewMapLink');
        const applyManualPinBtn = document.getElementById('applyManualPinBtn');
        const clearPinBtn = document.getElementById('clearPinBtn');

        function getCleanCoordinate(value) {
            if (value === '' || value === null || value === undefined) {
                return null;
            }

            const numericValue = Number.parseFloat(value);

            return Number.isFinite(numericValue) ? numericValue.toFixed(7) : null;
        }

        function updateLocationPreview(latitude, longitude) {
            if (latitude && longitude) {
                locationStatus.textContent = `Location pin ready: ${latitude}, ${longitude}`;
                previewMapLink.href = `https://www.google.com/maps?q=${latitude},${longitude}`;
                previewMapLink.classList.remove('d-none');
                return;
            }

            locationStatus.textContent = 'No location pin added yet.';
            previewMapLink.href = '#';
            previewMapLink.classList.add('d-none');
        }

        function applyManualPin() {
            const latitude = getCleanCoordinate(latitudeInput.value);
            const longitude = getCleanCoordinate(longitudeInput.value);

            if (! latitude || ! longitude) {
                locationStatus.textContent = 'Enter both latitude and longitude to set a manual pin.';
                previewMapLink.href = '#';
                previewMapLink.classList.add('d-none');
                return;
            }

            latitudeInput.value = latitude;
            longitudeInput.value = longitude;
            updateLocationPreview(latitude, longitude);
        }

        updateLocationPreview(latitudeInput.value, longitudeInput.value);

        applyManualPinBtn?.addEventListener('click', applyManualPin);

        clearPinBtn?.addEventListener('click', function () {
            latitudeInput.value = '';
            longitudeInput.value = '';
            updateLocationPreview(null, null);
        });

        latitudeInput?.addEventListener('change', applyManualPin);
        longitudeInput?.addEventListener('change', applyManualPin);

        locationButton?.addEventListener('click', function () {
            if (! navigator.geolocation) {
                locationStatus.textContent = 'Geolocation is not supported by this browser.';
                return;
            }

            locationStatus.textContent = 'Getting your current location...';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const latitude = position.coords.latitude.toFixed(7);
                    const longitude = position.coords.longitude.toFixed(7);

                    latitudeInput.value = latitude;
                    longitudeInput.value = longitude;
                    updateLocationPreview(latitude, longitude);
                },
                function () {
                    locationStatus.textContent = 'Unable to get your current location. Please allow location access and try again.';
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
