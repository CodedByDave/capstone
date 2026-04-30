<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaymentQrController extends Controller
{
    private function qrUrl(?string $value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'http') ? $value : Storage::disk('public')->url($value);
    }

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return $user->shop;
        }

        $shopId = Employee::where('user_id', $user->id)->value('shop_id');
        return Shop::findOrFail($shopId);
    }

    public function index(): Response
    {
        $shop = $this->getShop();

        return Inertia::render('shop/PaymentQr', [
            'paymongo_configured' => $shop->hasPaymongo(),
            'paymongo_public_key' => $shop->paymongo_public_key,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'gcash_qr' => ['nullable', 'image', 'max:2048'],
            'maya_qr'  => ['nullable', 'image', 'max:2048'],
        ]);

        $shop = $this->getShop();

        foreach (['gcash_qr', 'maya_qr'] as $field) {
            if ($request->hasFile($field)) {
                // Remove old file if exists
                if ($shop->$field) {
                    Storage::disk('public')->delete($shop->$field);
                }
                $path = $request->file($field)->store("qr/{$shop->id}", 'public');
                $shop->$field = $path;
            }

            // Allow clearing a QR
            if ($request->input("remove_{$field}")) {
                if ($shop->$field) {
                    Storage::disk('public')->delete($shop->$field);
                }
                $shop->$field = null;
            }
        }

        $shop->save();

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Payment QR codes updated.',
        ]);
    }
}
