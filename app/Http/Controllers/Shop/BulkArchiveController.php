<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkIdsRequest;
use App\Models\Employee;
use App\Models\Shop;
use App\Services\BulkArchiveService;

/**
 * Bulk archive / restore for the shop tables. The resource name comes from the route's defaults,
 * never from user input.
 */
class BulkArchiveController extends Controller
{
    public function __construct(private readonly BulkArchiveService $bulk) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        return Shop::findOrFail($employee->shop_id);
    }

    public function archive(BulkIdsRequest $request, string $resource)
    {
        $count = $this->bulk->archive($resource, $request->ids(), $this->getShop());

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$count} record(s) archived.",
        ]);
    }

    public function restore(BulkIdsRequest $request, string $resource)
    {
        $count = $this->bulk->restore($resource, $request->ids(), $this->getShop());

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$count} record(s) restored.",
        ]);
    }
}
