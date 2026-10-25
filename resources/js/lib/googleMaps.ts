import type { LatLng } from '@/lib/maps';

export interface GoogleRoute {
    distanceText: string;
    durationText: string;
    distanceMeters: number;
    durationSeconds: number;
}

let loader: Promise<any> | null = null;

/**
 * Loads the Google Maps JavaScript API once. Rejects when the script cannot load, so callers
 * can fall back to a plain "open in Google Maps" link.
 */
export function loadGoogleMaps(apiKey: string): Promise<any> {
    const existing = (window as any).google?.maps;
    if (existing) return Promise.resolve((window as any).google);

    loader ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&v=weekly`;
        script.async = true;
        script.onload = () => resolve((window as any).google);
        script.onerror = () => {
            loader = null;
            reject(new Error('Google Maps could not be loaded.'));
        };
        document.head.appendChild(script);
    });

    return loader;
}

/** A Google Maps link that opens turn-by-turn navigation to the stop. No API key is needed. */
export function googleMapsNavigationUrl(destination: {
    lat: number | null;
    lng: number | null;
    address?: string | null;
}): string {
    const hasPin = destination.lat !== null && destination.lng !== null;
    const target = hasPin
        ? `${destination.lat},${destination.lng}`
        : (destination.address ?? '').trim();

    const url = new URL('https://www.google.com/maps/dir/');
    url.searchParams.set('api', '1');
    url.searchParams.set('travelmode', 'driving');
    if (target !== '') url.searchParams.set('destination', target);

    return url.toString();
}

/** Whether the stop has anything Google Maps can navigate to. */
export function canNavigateTo(destination: {
    lat: number | null;
    lng: number | null;
    address?: string | null;
}): boolean {
    return (
        (destination.lat !== null && destination.lng !== null) ||
        (destination.address ?? '').trim() !== ''
    );
}

/** Asks the Directions service for the driving route; resolves null when no road route exists. */
export function requestRoute(
    google: any,
    origin: LatLng,
    destination: LatLng,
): Promise<{ result: any; summary: GoogleRoute } | null> {
    const service = new google.maps.DirectionsService();

    return new Promise((resolve) => {
        service.route(
            {
                origin,
                destination,
                travelMode: google.maps.TravelMode.DRIVING,
            },
            (result: any, status: string) => {
                const leg = result?.routes?.[0]?.legs?.[0];
                if (status !== 'OK' || !leg) {
                    resolve(null);
                    return;
                }

                resolve({
                    result,
                    summary: {
                        distanceText: leg.distance.text,
                        durationText: leg.duration.text,
                        distanceMeters: leg.distance.value,
                        durationSeconds: leg.duration.value,
                    },
                });
            },
        );
    });
}
