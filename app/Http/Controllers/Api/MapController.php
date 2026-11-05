<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DeliveryNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Map\DestinationRequest;
use App\Http\Requests\Map\GeocodeRequest;
use App\Http\Requests\Map\OptimizeRouteRequest;
use App\Http\Requests\Map\ReverseGeocodeRequest;
use App\Services\MapboxService;
use App\Services\ShopMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(
        private readonly MapboxService $mapbox,
        private readonly ShopMapService $maps,
    ) {}

    public function geocode(GeocodeRequest $request): JsonResponse
    {
        $results = $this->mapbox->geocode(
            $request->validated('q'),
            $request->filled('lat') ? (float) $request->validated('lat') : null,
            $request->filled('lng') ? (float) $request->validated('lng') : null,
        );

        return response()->json(['results' => $results]);
    }

    public function reverseGeocode(ReverseGeocodeRequest $request): JsonResponse
    {
        $place = $this->mapbox->reverseGeocode((float) $request->validated('lat'), (float) $request->validated('lng'));

        return response()->json($place ?? [
            'address' => null,
            'place' => null,
            'locality' => null,
            'neighborhood' => null,
            'street' => null,
        ]);
    }

    public function route(Request $request, DestinationRequest $destination, string $shop): JsonResponse
    {
        $shop = $this->maps->shopFor($request->user(), $shop);

        try {
            return response()->json($this->maps->route($shop, $destination->destinationLat(), $destination->destinationLng()));
        } catch (DeliveryNotAvailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function feeQuote(Request $request, DestinationRequest $destination, string $shop): JsonResponse
    {
        $shop = $this->maps->shopFor($request->user(), $shop);

        try {
            return response()->json([
                'available' => true,
                ...$this->maps->feeQuote($shop, $destination->destinationLat(), $destination->destinationLng()),
            ]);
        } catch (DeliveryNotAvailableException $exception) {
            return response()->json(['available' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function optimize(OptimizeRouteRequest $request, string $shop): JsonResponse
    {
        $shop = $this->maps->shopFor($request->user(), $shop);

        try {
            return response()->json($this->maps->optimize($shop, $request->stops()));
        } catch (DeliveryNotAvailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
