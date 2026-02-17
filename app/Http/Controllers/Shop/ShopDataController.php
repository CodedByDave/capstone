<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopDataController extends Controller
{
    public function getShop()
    {
        // Get the shop for the currently logged-in owner
        $shop = Auth::user()->shop;

        return response()->json([
            'id' => $shop->id,
            'branch_name' => $shop->branch_name,
            'shop_name' => $shop->shop_name,
            'owner_name' => $shop->owner_name,
            'email' => Auth::user()->email,
            'phone' => $shop->phone,
            'block_street' => $shop->block_street,
            'municipality' => $shop->municipality,
            'barangay' => $shop->barangay,
            'postal_code' => $shop->postal_code,
        ]);
    }
}
