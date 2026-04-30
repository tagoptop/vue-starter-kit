<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutgoingProductStatusRequest;
use App\Http\Requests\OutgoingProductStoreRequest;
use App\Models\OutgoingProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OutgoingProductController extends Controller
{
    /**
     * Show outgoing products with checker workflow state.
     */
    public function index(Request $request): Response
    {
        $products = OutgoingProduct::query()
            ->with(['preparedBy:id,name,role', 'checkedBy:id,name,role'])
            ->latest()
            ->get();

        return Inertia::render('OutgoingProducts/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Store a new outgoing product record created by staff.
     */
    public function store(OutgoingProductStoreRequest $request): RedirectResponse
    {
        OutgoingProduct::create([
            ...$request->validated(),
            'status' => 'draft',
            'prepared_by' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Outgoing product created.')]);

        return to_route('outgoing-products.index');
    }

    /**
     * Checker confirms product release.
     */
    public function release(OutgoingProductStatusRequest $request, OutgoingProduct $outgoingProduct): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isChecker()) {
            abort(403, 'Only a checker can release outgoing products.');
        }

        if ($outgoingProduct->status !== 'draft') {
            return back()->withErrors([
                'status' => __('Only draft products can be released.'),
            ]);
        }

        $outgoingProduct->update([
            'status' => 'released',
            'checked_by' => $user->id,
            'released_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product release has been checked.')]);

        return to_route('outgoing-products.index');
    }

    /**
     * Checker confirms product delivery.
     */
    public function deliver(OutgoingProductStatusRequest $request, OutgoingProduct $outgoingProduct): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isChecker()) {
            abort(403, 'Only a checker can mark delivery complete.');
        }

        if ($outgoingProduct->status !== 'released') {
            return back()->withErrors([
                'status' => __('Only released products can be marked as delivered.'),
            ]);
        }

        $outgoingProduct->update([
            'status' => 'delivered',
            'checked_by' => $user->id,
            'delivered_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Product delivery has been checked.')]);

        return to_route('outgoing-products.index');
    }
}
