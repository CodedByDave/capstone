<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterShopRequest;
use App\Repositories\ShopRepository;
use Inertia\Inertia;

class ShopRegisterController extends Controller
{
    public function __construct(private ShopRepository $shops) {}

    public function create()
    {
        return Inertia::render('auth/RegisterShop');
    }

    public function store(RegisterShopRequest $request)
    {
        $shop = $this->shops->createShopWithOwner($request->validated());

        return redirect()->route('login')->with('success', 'Shop registered successfully! Use your owner email to login.');
    }
}
