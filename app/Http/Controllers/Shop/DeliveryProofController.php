<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Logistics\StoreDeliveryPhotosRequest;
use App\Models\Delivery;
use App\Models\DeliveryPhoto;
use App\Services\DeliveryProofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Delivery proof photos, served to the shop (owner and permitted staff) and to the
 * customer of the delivered order. Files are never exposed by a public URL.
 */
class DeliveryProofController extends Controller
{
    public function __construct(private readonly DeliveryProofService $proof) {}

    public function store(StoreDeliveryPhotosRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->proof->add($request->user(), $delivery, $request->file('photos'));

        return back()->with('toast', ['type' => 'success', 'message' => 'Proof photos uploaded.']);
    }

    public function show(Request $request, DeliveryPhoto $photo): StreamedResponse
    {
        return Storage::disk('private')->response($this->proof->pathForShop($request->user(), $photo));
    }

    public function destroy(Request $request, DeliveryPhoto $photo): RedirectResponse
    {
        $this->proof->remove($request->user(), $photo);

        return back()->with('toast', ['type' => 'success', 'message' => 'Proof photo removed.']);
    }

    public function showForCustomer(Request $request, DeliveryPhoto $photo): StreamedResponse
    {
        return Storage::disk('private')->response($this->proof->pathForCustomer($request->user(), $photo));
    }
}
