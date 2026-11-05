<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Services\OrderWeightApprovalService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** The weighing photo, served only through the shop-scoped order route. */
class OrderWeightProofController extends Controller
{
    public function __construct(private readonly OrderWeightApprovalService $approvals) {}

    public function show(ShopOrder $order): StreamedResponse
    {
        return Storage::disk('private')->response($this->approvals->proofPath($order));
    }
}
