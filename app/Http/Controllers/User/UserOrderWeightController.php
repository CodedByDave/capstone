<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Services\OrderWeightApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserOrderWeightController extends Controller
{
    public function __construct(private readonly OrderWeightApprovalService $approvals) {}

    public function approve(Request $request, ShopOrder $order): RedirectResponse
    {
        $this->approvals->approve($request->user(), $order);

        return back()->with('toast', ['type' => 'success', 'message' => 'Weight approved. The shop can start your laundry.']);
    }

    public function decline(Request $request, ShopOrder $order): RedirectResponse
    {
        $this->approvals->decline($request->user(), $order);

        return redirect()->route('user.orders.index')->with('toast', [
            'type' => 'success',
            'message' => "Order {$order->order_number} has been cancelled.",
        ]);
    }

    public function proof(Request $request, ShopOrder $order): StreamedResponse
    {
        return Storage::disk('private')->response($this->approvals->proofPathForCustomer($request->user(), $order));
    }
}
