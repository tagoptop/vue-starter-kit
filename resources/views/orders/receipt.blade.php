@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <h4 class="mb-0">Delivery Receipt</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">Back to Order</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print Receipt</button>
    </div>
</div>

<div class="receipt-sheet bg-white border rounded-3 p-4 p-md-5">
    <div class="text-center mb-3">
        <div class="fw-bold fs-4">CJB HOLLOW BLOCKS AND CONSTRUCTION SUPPLIES</div>
        <div class="small">TAMAK, PADRE GARCIA, BATANGAS</div>
        <div class="small">TEL. NO. (043) 515-95-03</div>
        <div class="small">09551447541 / 09926292422</div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div class="fw-bold fs-5">DELIVERY RECEIPT</div>
        <div>
            <div><strong>No.</strong> {{ $receiptNumber }}</div>
            <div class="small text-muted">Order {{ $order->order_number }}</div>
        </div>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-md-8">
            <div class="receipt-line"><strong>DELIVERED TO:</strong> {{ $order->customer?->name ?? '____________________' }}</div>
        </div>
        <div class="col-md-4">
            <div class="receipt-line"><strong>DATE:</strong> {{ ($order->delivered_at ?? $order->scheduled_for ?? now())->format('Y-m-d') }}</div>
        </div>
        <div class="col-12">
            <div class="receipt-line"><strong>ADDRESS:</strong> {{ $order->delivery_address ?: '____________________' }}</div>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-bordered receipt-table mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 12%;">QUANTITY</th>
                    <th class="text-center" style="width: 10%;">UNIT</th>
                    <th style="width: 38%;">DESCRIPTION</th>
                    <th class="text-end" style="width: 18%;">UNIT PRICE</th>
                    <th class="text-end" style="width: 22%;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $maxRows = 18;
                    $rowsUsed = $order->items->count();
                @endphp
                @foreach($order->items as $item)
                    <tr>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">pc</td>
                        <td>{{ $item->product?->name ?? 'Unknown item' }}</td>
                        <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach

                @for($i = $rowsUsed; $i < $maxRows; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor

                <tr>
                    <td colspan="4" class="text-end fw-bold">SUBTOTAL</td>
                    <td class="text-end fw-bold">{{ number_format((float) ($order->subtotal_amount ?? $order->total_amount), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold">DISCOUNT</td>
                    <td class="text-end fw-bold">{{ number_format((float) ($order->discount_amount ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL</td>
                    <td class="text-end fw-bold">{{ number_format((float) $order->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="small mb-4">Received the above articles in good order and condition.</div>

    <div class="d-flex justify-content-between align-items-end gap-3">
        <div>
            <div>_____ Pick-up</div>
            <div>_____ Deliver</div>
        </div>
        <div class="text-end">
            <div class="signature-line">&nbsp;</div>
            <div class="small">Signature</div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .receipt-sheet {
        max-width: 900px;
        margin: 0 auto;
        font-family: "Arial Narrow", Arial, sans-serif;
        color: #111827;
    }

    .receipt-line {
        border-bottom: 1px solid #1f2937;
        min-height: 1.8rem;
        padding: 0.2rem 0.35rem;
        font-size: 0.95rem;
    }

    .receipt-table th,
    .receipt-table td {
        font-size: 0.88rem;
        vertical-align: middle;
    }

    .receipt-table td {
        height: 1.8rem;
    }

    .signature-line {
        width: 220px;
        border-bottom: 1px solid #1f2937;
        margin-left: auto;
        margin-bottom: 0.2rem;
    }

    @media print {
        .navbar,
        .no-print,
        .alert,
        footer {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        main.container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .receipt-sheet {
            border: none !important;
            box-shadow: none !important;
            margin: 0;
            padding: 0.25in;
        }
    }
</style>
@endpush
