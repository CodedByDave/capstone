<?php

namespace App\Http\Controllers\User;

use App\Exceptions\OrderRatingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreOrderRatingRequest;
use App\Services\OrderRatingService;
use Illuminate\Http\RedirectResponse;

class UserOrderRatingController extends Controller
{
    public function __construct(private readonly OrderRatingService $ratings) {}

    public function store(StoreOrderRatingRequest $request, string $paymentPublicId): RedirectResponse
    {
        try {
            $this->ratings->submit(
                $request->user(),
                $paymentPublicId,
                $request->integer('rating'),
                $request->validated('comment'),
            );
        } catch (OrderRatingException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Thanks for rating your order!']);
    }
}
