<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use Inertia\Inertia;

class ShopOrderController extends Controller
{
    public function displayModules()
    {
        $modules = Module::select('id', 'name', 'description', 'price')->get();

        return Inertia::render('shop/Dashboard', [
            'modules' => $modules,
        ]);
    }
}
