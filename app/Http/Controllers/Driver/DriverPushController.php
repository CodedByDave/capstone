<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\DestroyPushSubscriptionRequest;
use App\Http\Requests\Driver\StorePushSubscriptionRequest;
use App\Services\Driver\DriverPushService;
use Illuminate\Http\JsonResponse;

/** Registers or removes the push subscription of the driver's current device. */
class DriverPushController extends Controller
{
    public function __construct(private readonly DriverPushService $service) {}

    public function key(): JsonResponse
    {
        return response()->json(['public_key' => $this->service->publicKey()]);
    }

    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $this->service->subscribe($request->user(), $request->validated());

        return response()->json(['ok' => true]);
    }

    public function destroy(DestroyPushSubscriptionRequest $request): JsonResponse
    {
        $this->service->unsubscribe($request->user(), $request->validated('endpoint'));

        return response()->json(['ok' => true]);
    }
}
