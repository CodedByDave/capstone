<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\UpdateOrderAutomationRequest;
use App\Services\OrderAutomationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The owner's rules for moving orders through their statuses automatically (paid plans only). */
class OrderAutomationController extends Controller
{
    public function __construct(private readonly OrderAutomationSettingsService $automation) {}

    public function edit(Request $request): Response
    {
        return Inertia::render(
            'shop/operations/automation/Index',
            $this->automation->pageData($request->user()),
        );
    }

    public function update(UpdateOrderAutomationRequest $request): RedirectResponse
    {
        $this->automation->update($request->user(), $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Order automation settings saved.']);
    }
}
