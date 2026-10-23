<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Exceptions\OrderComplaintException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ComplaintFilterRequest;
use App\Http\Requests\Shop\RespondToComplaintRequest;
use App\Services\OrderComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The shop's view of problems customers reported. Serves /shop/operations/complaints (owner) and
 * /staff/operations/complaints (staff); the route decides who may view or respond.
 */
class OrderComplaintController extends Controller
{
    public function __construct(private readonly OrderComplaintService $complaints) {}

    public function index(ComplaintFilterRequest $request): Response
    {
        return Inertia::render(
            'shop/operations/complaints/Index',
            $this->complaints->shopPageData($request->user(), $request->filters()),
        );
    }

    public function respond(RespondToComplaintRequest $request, string $complaint): RedirectResponse
    {
        try {
            $this->complaints->respond(
                $request->user(),
                $complaint,
                $request->validated('status'),
                $request->validated('reply'),
            );
        } catch (OrderComplaintException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Your response was sent to the customer.']);
    }

    public function photo(Request $request, string $photo): StreamedResponse
    {
        return Storage::disk('private')->response(
            $this->complaints->photoPathForShop($request->user(), $photo),
            null,
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
        );
    }
}
