<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\PlatformRatingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\DismissPlatformRatingRequest;
use App\Http\Requests\Shop\StorePlatformRatingRequest;
use App\Services\PlatformRatingService;
use Illuminate\Http\RedirectResponse;

/** The shop owner rating the LaundryHub platform. */
class PlatformRatingController extends Controller
{
    public function __construct(private readonly PlatformRatingService $ratings) {}

    public function store(StorePlatformRatingRequest $request): RedirectResponse
    {
        try {
            $this->ratings->submit($request->user(), $request->integer('rating'), $request->validated('comment'));
        } catch (PlatformRatingException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Thanks for rating LaundryHub!']);
    }

    public function dismiss(DismissPlatformRatingRequest $request): RedirectResponse
    {
        $this->ratings->dismiss($request->user());

        return back();
    }
}
