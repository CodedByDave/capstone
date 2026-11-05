<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilePhotoRequest;
use App\Services\ProfilePhotoService;
use Illuminate\Http\RedirectResponse;

class ProfilePhotoController extends Controller
{
    public function __construct(private readonly ProfilePhotoService $service) {}

    public function update(UpdateProfilePhotoRequest $request): RedirectResponse
    {
        $this->service->update($request->user(), $request->file('photo'));

        return back()->with('toast', ['type' => 'success', 'message' => 'Profile photo updated.']);
    }
}
