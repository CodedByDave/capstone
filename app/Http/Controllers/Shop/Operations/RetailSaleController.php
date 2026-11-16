<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\RetailSaleFilterRequest;
use App\Http\Requests\Shop\Operations\StoreRetailSaleRequest;
use App\Http\Resources\RetailSaleResource;
use App\Services\RetailSaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Retail products sold on their own (counter sales and online product orders), apart from laundry orders. */
class RetailSaleController extends Controller
{
    public function __construct(private readonly RetailSaleService $sales) {}

    private function base(Request $request): string
    {
        return $request->user()->isOwner() ? '/shop' : '/staff';
    }

    public function index(RetailSaleFilterRequest $request): Response
    {
        $listing = $this->sales->listing($request->user(), $request->filters());

        return Inertia::render('shop/operations/retail/Index', [
            'sales' => $listing['sales']->through(fn ($sale) => (new RetailSaleResource($sale))->resolve()),
            'summary' => $listing['summary'],
            'filters' => $request->filters(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('shop/operations/retail/Create', $this->sales->formData($request->user()));
    }

    public function store(StoreRetailSaleRequest $request): RedirectResponse
    {
        $order = $this->sales->record($request->user(), $request->validated());

        // The order page opens the receipt, so staff can hand it to the customer.
        return redirect("{$this->base($request)}/operations/orders/{$order->payment_public_id}")
            ->with('show_receipt', true)
            ->with('toast', [
                'type' => 'success',
                'message' => "Sale {$order->order_number} recorded.",
            ]);
    }
}
