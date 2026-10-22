<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ShopRatingFilterRequest;
use App\Services\OrderRatingService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The shop's view of what customers rated it. Serves /shop/operations/ratings (owner) and
 * /staff/operations/ratings (staff with Operations view); the route decides who may open it.
 */
class OrderRatingController extends Controller
{
    public function __construct(private readonly OrderRatingService $ratings) {}

    public function index(ShopRatingFilterRequest $request): Response
    {
        return Inertia::render(
            'shop/operations/ratings/Index',
            $this->ratings->shopPageData($request->user(), $request->filters()),
        );
    }
}
