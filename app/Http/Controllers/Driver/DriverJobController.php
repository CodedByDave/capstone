<?php

namespace App\Http\Controllers\Driver;

use App\Exceptions\DriverJobException;
use App\Exceptions\RiderLocationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\SetAvailabilityRequest;
use App\Http\Requests\Driver\UpdateJobStatusRequest;
use App\Http\Requests\Driver\UpdateRiderLocationRequest;
use App\Services\Driver\DriverAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/** What a driver does: go online or offline, move a run forward, share their position. */
class DriverJobController extends Controller
{
    public function __construct(private readonly DriverAppService $service) {}

    public function setAvailability(SetAvailabilityRequest $request): RedirectResponse
    {
        try {
            $rider = $this->service->setOnline(
                $request->attributes->get('rider'),
                $request->boolean('online'),
            );
        } catch (DriverJobException $e) {
            return back()->with('toast', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $rider->is_online ? 'You are online.' : 'You are offline.',
        ]);
    }

    public function updateStatus(UpdateJobStatusRequest $request, string $job): RedirectResponse
    {
        try {
            $this->service->updateStatus(
                $request->attributes->get('rider'),
                $job,
                $request->validated('status'),
                $request->validated('notes'),
            );
        } catch (DriverJobException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Status updated.']);
    }

    public function updateLocation(UpdateRiderLocationRequest $request, string $job): JsonResponse
    {
        $data = $request->validated();

        try {
            $delivery = $this->service->updateLocation(
                $request->attributes->get('rider'),
                $job,
                (float) $data['lat'],
                (float) $data['lng'],
            );
        } catch (RiderLocationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Location updated.',
            'located_at' => $delivery->rider_located_at?->toIso8601String(),
        ]);
    }
}
