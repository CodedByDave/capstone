<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Operations\CustomerFilterRequest;
use App\Http\Requests\Shop\Operations\CustomerSearchRequest;
use App\Services\ShopCustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The shop's customers: one record per person, with the orders they placed. */
class ShopCustomerController extends Controller
{
    public function __construct(private readonly ShopCustomerService $customers) {}

    public function index(CustomerFilterRequest $request): Response
    {
        $listing = $this->customers->listing($request->user(), $request->filters());

        return Inertia::render('shop/operations/customers/Index', [
            'customers' => $listing['customers'],
            'summary' => $listing['summary'],
            'filters' => $request->filters(),
        ]);
    }

    public function show(Request $request, string $publicId): Response
    {
        $profile = $this->customers->profile($request->user(), $publicId);

        return Inertia::render('shop/operations/customers/Show', [
            'customer' => $profile['customer']->only(['public_id', 'name', 'phone', 'email', 'created_at']),
            'stats' => $profile['stats'],
            'orders' => $profile['orders'],
        ]);
    }

    /** Suggestions while typing a customer's name or number on the order form. */
    public function search(CustomerSearchRequest $request): JsonResponse
    {
        return response()->json(['customers' => $this->customers->suggestions($request->user(), $request->term())]);
    }
}
