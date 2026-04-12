<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Notifications\DeliveryNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverPageController extends Controller
{
    public function show(string $token)
    {
        $delivery = Delivery::where('driver_token', $token)
            ->with(['order:id,order_number', 'rider:id,name,phone,vehicle_type', 'shop:id,shop_name'])
            ->firstOrFail();

        return Inertia::render('driver/DeliveryStatus', [
            'delivery' => [
                'id'               => $delivery->id,
                'token'            => $token,
                'status'           => $delivery->status,
                'customer_name'    => $delivery->customer_name,
                'customer_phone'   => $delivery->customer_phone,
                'delivery_address' => $delivery->delivery_address,
                'notes'            => $delivery->notes,
                'order_number'     => $delivery->order?->order_number,
                'shop_name'        => $delivery->shop?->shop_name,
                'rider_name'       => $delivery->rider?->name,
                'assigned_at'      => $delivery->assigned_at?->toISOString(),
                'picked_up_at'     => $delivery->picked_up_at?->toISOString(),
                'delivered_at'     => $delivery->delivered_at?->toISOString(),
            ],
        ]);
    }

    public function updateStatus(Request $request, string $token)
    {
        $delivery = Delivery::where('driver_token', $token)
            ->with(['order.customer', 'rider'])
            ->firstOrFail();

        $data = $request->validate([
            'status' => ['required', 'in:picked_up,delivered,failed'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        // Prevent going backwards
        $order        = ['assigned', 'picked_up', 'delivered', 'failed'];
        $currentIndex = array_search($delivery->status, $order);
        $newIndex     = array_search($data['status'], $order);

        if ($newIndex === false || $newIndex <= $currentIndex) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => "Cannot change from '{$delivery->status}' to '{$data['status']}'.",
            ]);
        }

        $timestamps = [];
        if ($data['status'] === 'picked_up' && !$delivery->picked_up_at) {
            $timestamps['picked_up_at'] = now();
        }
        if ($data['status'] === 'delivered' && !$delivery->delivered_at) {
            $timestamps['delivered_at'] = now();
        }

        $delivery->update(array_merge([
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $delivery->notes,
        ], $timestamps));

        // Notify the customer
        $customer = $delivery->order?->customer;
        if ($customer) {
            $customer->notify(new DeliveryNotification($delivery));
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Status updated successfully.']);
    }
}
