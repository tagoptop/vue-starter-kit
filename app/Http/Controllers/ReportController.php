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
        [$start, $end] = $this->resolveRange($range);

        $orders = Order::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['approved', 'delivered'])
            ->latest()
            ->get();

        $salesTotal = $orders->sum('total_amount');

        return view('reports.index', compact('orders', 'salesTotal', 'range', 'start', 'end'));
    }

    public function exportExcel(Request $request)
    {
        $range = $request->get('range', 'daily');
        [$start, $end] = $this->resolveRange($range);

        $orders = Order::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['approved', 'delivered'])
            ->get();

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
        [$start, $end] = $this->resolveRange($range);

        $orders = Order::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['approved', 'delivered'])
            ->latest()
            ->get();

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
