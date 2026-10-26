<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\UpdateDeliveryPricingRequest;
use App\Http\Requests\Shop\UpdateDriverPayRequest;
use App\Http\Requests\Shop\UpdateOperatingHoursRequest;
use App\Http\Requests\Shop\UpdatePaymongoSettingsRequest;
use App\Models\Shop;
use App\Services\ShopDeliveryPricingService;
use App\Services\ShopOperatingHoursService;
use App\Services\ShopPaymentSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ShopSettingsController extends Controller
{
    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    private function coverPhotoUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return str_starts_with($value, 'http') ? $value : Storage::disk('public')->url($value);
    }

    public function index(ShopDeliveryPricingService $deliveryPricing, ShopOperatingHoursService $operatingHours)
    {
        $shop = $this->getShop();

        return Inertia::render('shop/Settings', [
            'deliveryPricing' => $deliveryPricing->pageData(auth()->user()),
            'operatingHours' => $operatingHours->pageData(auth()->user()),
            'shop' => [
                'shop_name' => $shop->shop_name,
                'phone' => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay' => $shop->barangay,
                'postal_code' => $shop->postal_code,
                'latitude' => $shop->latitude,
                'longitude' => $shop->longitude,
                'status' => $shop->status,
                'cover_photo' => $this->coverPhotoUrl($shop->cover_photo),
            ],
        ]);
    }

    public function updatePaymongo(
        UpdatePaymongoSettingsRequest $request,
        ShopPaymentSettingsService $paymentSettings,
    ) {
        return back()->with('toast', $paymentSettings->update($request->user(), $request->validated()));
    }

    public function updateOperatingHours(
        UpdateOperatingHoursRequest $request,
        ShopOperatingHoursService $operatingHours,
    ): RedirectResponse {
        $operatingHours->update($request->user(), $request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Operating hours saved.',
        ]);
    }

    public function updateDriverPay(
        UpdateDriverPayRequest $request,
        ShopDeliveryPricingService $deliveryPricing,
    ): RedirectResponse {
        $deliveryPricing->updateDriverPay($request->user(), $request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Driver earnings setting saved.',
        ]);
    }

    public function updateDeliveryPricing(
        UpdateDeliveryPricingRequest $request,
        ShopDeliveryPricingService $deliveryPricing,
    ): RedirectResponse {
        $deliveryPricing->update($request->user(), $request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Delivery pricing saved.',
        ]);
    }

    public function updateCoverPhoto(Request $request)
    {
        $request->validate([
            'cover_photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $shop = $this->getShop();

        if ($request->hasFile('cover_photo')) {
            if ($shop->cover_photo) {
                Storage::disk('public')->delete($shop->cover_photo);
            }
            $path = $request->file('cover_photo')->store("covers/{$shop->id}", 'public');
            $shop->cover_photo = $path;
            $shop->save();
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cover photo updated.',
        ]);
    }

    public function removeCoverPhoto()
    {
        $shop = $this->getShop();

        if ($shop->cover_photo) {
            Storage::disk('public')->delete($shop->cover_photo);
            $shop->cover_photo = null;
            $shop->save();
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Cover photo removed.',
        ]);
    }

    public function updateGeo(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $shop = $this->getShop();
        $shop->update([
            'latitude' => $data['latitude'] !== null ? (float) $data['latitude'] : null,
            'longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Shop location updated successfully.',
        ]);
    }

    public function clearGeo()
    {
        $shop = $this->getShop();
        $shop->update(['latitude' => null, 'longitude' => null]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Shop location cleared.',
        ]);
    }
}
