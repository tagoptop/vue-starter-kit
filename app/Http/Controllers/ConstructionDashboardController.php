<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class ConstructionDashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $salesSummary = Order::whereIn('status', ['approved', 'delivered'])->sum('total_amount');
        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        return view('dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalCustomers',
            'salesSummary',
            'lowStockCount'
        ));
    }
}
