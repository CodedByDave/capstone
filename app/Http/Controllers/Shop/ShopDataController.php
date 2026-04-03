<?php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShopDataController extends Controller
{
    public function getShop()
    {
        $user = Auth::user();
        $shop = $user->shop()->first();
        if (!$shop) {
            return response()->json(['message' => 'No shop linked to this account.'], 404);
        }

        return response()->json([
            'owner_name'   => $user->name,
            'email'        => $user->email,
            'shop_name'    => $shop->shop_name,
            'phone'        => $shop->phone,
            'branch_name'  => $shop->branch_name,
            'block_street' => $shop->block_street,
            'municipality' => $shop->municipality,
            'barangay'     => $shop->barangay,
            'postal_code'  => $shop->postal_code,
        ]);
    }
}
