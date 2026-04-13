<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h2>Construction Supply - Sales Report ({{ ucfirst($range) }})</h2>
    <p>Period: {{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }}</p>
    <p><strong>Total Sales:</strong> ₱{{ number_format($salesTotal, 2) }}</p>

    <table>
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
            @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->name }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
