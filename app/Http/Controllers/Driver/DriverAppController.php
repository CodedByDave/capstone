<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\Driver\DriverAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The pages of the driver app; a driver only ever sees their own runs. */
class DriverAppController extends Controller
{
    public function __construct(private readonly DriverAppService $service) {}

    public function home(Request $request): Response
    {
        return Inertia::render('driver/Home', $this->service->home($request->attributes->get('rider')));
    }

    public function jobs(Request $request): Response
    {
        return Inertia::render('driver/Jobs', $this->service->jobs($request->attributes->get('rider')));
    }

    public function show(Request $request, string $job): Response
    {
        return Inertia::render('driver/JobDetail', [
            ...$this->service->job($request->attributes->get('rider'), $job),
            'maps_key' => config('services.google.maps_key'),
        ]);
    }

    public function map(Request $request): Response
    {
        return Inertia::render('driver/Map', [
            ...$this->service->map($request->attributes->get('rider')),
            'maps_key' => config('services.google.maps_key'),
        ]);
    }

    public function earnings(Request $request): Response
    {
        return Inertia::render('driver/Earnings', $this->service->earningsPage($request->attributes->get('rider')));
    }

    public function profile(Request $request): Response
    {
        return Inertia::render('driver/Profile', $this->service->profile($request->attributes->get('rider')));
    }

    /** The manifest that lets the driver app be installed on its own, apart from the main site. */
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'LaundryHub Driver',
            'short_name' => 'LH Driver',
            'description' => 'Pickups and deliveries for LaundryHub drivers',
            'id' => '/driver',
            'start_url' => '/driver',
            'scope' => '/driver',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'theme_color' => '#0b3d91',
            'background_color' => '#f3f4f6',
            'icons' => [
                ['src' => '/pwa-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/pwa-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => '/pwa-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
