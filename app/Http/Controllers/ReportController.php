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

        $orders = $query->latest()->get();

        $salesTotal = $orders->sum('total_amount');

        return view('reports.index', compact('orders', 'salesTotal', 'range', 'start', 'end', 'customFrom', 'customTo', 'status'));
    }

    public function exportExcel(Request $request)
    {
        $range = $request->get('range', 'daily');
        $customFrom = $request->get('from');
        $customTo = $request->get('to');
        $status = $request->get('status', 'all');

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

        $orders = $query->get();

        $csv = "Order Number,Customer,Status,Total,Date\n";

        foreach ($orders as $order) {
            $csv .= implode(',', [
                $order->order_number,
                '"' . str_replace('"', '""', $order->customer?->name ?? 'N/A') . '"',
                $order->status,
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

        $orders = $query->latest()->get();

        $salesTotal = $orders->sum('total_amount');

        $pdf = Pdf::loadView('reports.pdf', compact('orders', 'salesTotal', 'range', 'start', 'end'));

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
