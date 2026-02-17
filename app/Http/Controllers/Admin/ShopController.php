<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Shop;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function index()
    {
        $today = Carbon::today(); // today's date

        // Fetch all shops with owner
        $shops = Shop::with('owner')->get();

        // Stats
        $stats = [
            'today' => Shop::whereDate('created_at', $today)->count(), // now correct
            'total' => Shop::count(),
            'active' => Shop::where('status', 'active')->count(),
        ];

        return Inertia::render('admin/shop/Index', [
            'shops' => $shops,
            'stats' => $stats,
        ]);
    }
}
