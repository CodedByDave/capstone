<?php

namespace App\Http\Controllers\User;

use App\Exceptions\OrderComplaintException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreOrderComplaintRequest;
use App\Services\OrderComplaintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** A customer reporting a problem with one of their orders. */
class UserOrderComplaintController extends Controller
{
    public function __construct(private readonly OrderComplaintService $complaints) {}

    public function store(StoreOrderComplaintRequest $request, string $paymentPublicId): RedirectResponse
    {
        try {
            $this->complaints->file(
                $request->user(),
                $paymentPublicId,
                $request->validated('type'),
                $request->validated('description'),
                $request->photoFiles(),
            );
        } catch (OrderComplaintException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Your report was sent to the shop. We will notify you when they respond.',
        ]);
    }

    public function photo(Request $request, string $photo): StreamedResponse
    {
        return Storage::disk('private')->response(
            $this->complaints->photoPathForCustomer($request->user(), $photo),
            null,
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
        );
    }
}
