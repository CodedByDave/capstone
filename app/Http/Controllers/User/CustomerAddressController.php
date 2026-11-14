<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SaveCustomerAddressRequest;
use App\Http\Resources\CustomerAddressResource;
use App\Services\CustomerAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAddressController extends Controller
{
    public function __construct(private readonly CustomerAddressService $addresses) {}

    public function index(Request $request): Response
    {
        return Inertia::render('user/Addresses', [
            'addresses' => CustomerAddressResource::collection($this->addresses->listFor($request->user()))->resolve(),
            'limit' => CustomerAddressService::MAX_ADDRESSES,
        ]);
    }

    public function store(SaveCustomerAddressRequest $request): RedirectResponse
    {
        $this->addresses->create($request->user(), $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Address saved.']);
    }

    public function update(SaveCustomerAddressRequest $request, string $publicId): RedirectResponse
    {
        $this->addresses->update($request->user(), $publicId, $request->validated());

        return back()->with('toast', ['type' => 'success', 'message' => 'Address updated.']);
    }

    public function makeDefault(Request $request, string $publicId): RedirectResponse
    {
        $this->addresses->makeDefault($request->user(), $publicId);

        return back()->with('toast', ['type' => 'success', 'message' => 'Default address changed.']);
    }

    public function destroy(Request $request, string $publicId): RedirectResponse
    {
        $this->addresses->delete($request->user(), $publicId);

        return back()->with('toast', ['type' => 'success', 'message' => 'Address deleted.']);
    }
}
