<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformRatingFilterRequest;
use App\Services\PlatformRatingService;
use Inertia\Inertia;
use Inertia\Response;

/** What shop owners think of the LaundryHub platform. */
class PlatformRatingController extends Controller
{
    public function __construct(private readonly PlatformRatingService $ratings) {}

    public function index(PlatformRatingFilterRequest $request): Response
    {
        return Inertia::render(
            'admin/platform-ratings/Index',
            $this->ratings->adminPageData($request->filters()),
        );
    }
}
