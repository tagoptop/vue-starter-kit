<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->get('range', 'daily');
        $customFrom = $request->get('from');
        $customTo = $request->get('to');
        $status = $request->get('status', 'all');
        $paymentMethod = $request->get('payment_method', 'all');
        $paymentStatus = $request->get('payment_status', 'all');

        // Use custom dates if provided, otherwise use predefined ranges
        if ($customFrom && $customTo) {
            $start = Carbon::createFromFormat('Y-m-d', $customFrom)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $customTo)->endOfDay();
        } else {
            [$start, $end] = $this->resolveRange($range);
        }

        $query = Order::with('customer')
            ->whereBetween('created_at', [$start, $end]);

        // Apply status filter if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'delivered']);
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->latest()->get();

        $salesTotal = $orders->sum('total_amount');
        $collectedTotal = $orders->sum(function (Order $order): float {
            if ($order->payment_status !== 'paid') {
                return 0;
            }

            return (float) ($order->paid_amount ?? $order->total_amount);
        });

        return view('reports.index', compact(
            'orders',
            'salesTotal',
            'collectedTotal',
            'range',
            'start',
            'end',
            'customFrom',
            'customTo',
            'status',
            'paymentMethod',
            'paymentStatus'
        ));
    }

    public function exportExcel(Request $request)
    {
        $range = $request->get('range', 'daily');
        $customFrom = $request->get('from');
        $customTo = $request->get('to');
        $status = $request->get('status', 'all');
        $paymentMethod = $request->get('payment_method', 'all');
        $paymentStatus = $request->get('payment_status', 'all');

        // Use custom dates if provided, otherwise use predefined ranges
        if ($customFrom && $customTo) {
            $start = Carbon::createFromFormat('Y-m-d', $customFrom)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $customTo)->endOfDay();
        } else {
            [$start, $end] = $this->resolveRange($range);
        }

        $query = Order::with('customer')
            ->whereBetween('created_at', [$start, $end]);

        // Apply status filter if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'delivered']);
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->get();

        $csv = "Order Number,Customer,Status,Payment Method,Payment Status,Payment Reference,Paid Amount,Total,Date\n";

        foreach ($orders as $order) {
            $paidAmount = $order->payment_status === 'paid'
                ? (float) ($order->paid_amount ?? $order->total_amount)
                : 0;

            $csv .= implode(',', [
                $order->order_number,
                '"' . str_replace('"', '""', $order->customer?->name ?? 'N/A') . '"',
                $order->status,
                '"' . str_replace('"', '""', (string) ($order->payment_method ?? 'cod')) . '"',
                '"' . str_replace('"', '""', (string) ($order->payment_status ?? 'unpaid')) . '"',
                '"' . str_replace('"', '""', (string) ($order->payment_reference ?? '')) . '"',
                number_format($paidAmount, 2, '.', ''),
                number_format((float) $order->total_amount, 2, '.', ''),
                $order->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-' . $range . '.csv"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $range = $request->get('range', 'daily');
        $customFrom = $request->get('from');
        $customTo = $request->get('to');
        $status = $request->get('status', 'all');
        $paymentMethod = $request->get('payment_method', 'all');
        $paymentStatus = $request->get('payment_status', 'all');

        // Use custom dates if provided, otherwise use predefined ranges
        if ($customFrom && $customTo) {
            $start = Carbon::createFromFormat('Y-m-d', $customFrom)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $customTo)->endOfDay();
        } else {
            [$start, $end] = $this->resolveRange($range);
        }

        $query = Order::with('customer')
            ->whereBetween('created_at', [$start, $end]);

        // Apply status filter if specified
        if ($status !== 'all') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['approved', 'delivered']);
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->latest()->get();

        $salesTotal = $orders->sum('total_amount');
        $collectedTotal = $orders->sum(function (Order $order): float {
            if ($order->payment_status !== 'paid') {
                return 0;
            }

            return (float) ($order->paid_amount ?? $order->total_amount);
        });

        $pdf = Pdf::loadView('reports.pdf', compact('orders', 'salesTotal', 'collectedTotal', 'range', 'start', 'end'));

        return $pdf->download('sales-report-' . $range . '.pdf');
    }

    private function resolveRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
