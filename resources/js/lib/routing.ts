import type { LatLng } from '@/lib/maps';

/**
 * Road routing for the live tracking page. The public OSRM demo server is free and needs no key,
 * but it is meant for light use: point `VITE_ROUTING_URL` at your own OSRM (or a compatible
 * router) for heavy traffic. When routing is unreachable the page falls back to a straight line
 * and a speed-based estimate, so tracking still works.
 */
const ROUTING_URL = (
    import.meta.env.VITE_ROUTING_URL ?? 'https://router.project-osrm.org'
).replace(/\/$/, '');

const ROUTING_TIMEOUT_MS = 8_000;

export interface RoadRoute {
    /** The road geometry from start to end. */
    path: LatLng[];
    distanceMeters: number;
    durationSeconds: number;
    /** False when this is only a straight line with an estimated time. */
    isRoad: boolean;
}

const EARTH_RADIUS_M = 6_371_000;
const toRad = (deg: number) => (deg * Math.PI) / 180;
const toDeg = (rad: number) => (rad * 180) / Math.PI;

/** Straight-line distance between two points, in meters. */
export function distanceMeters(a: LatLng, b: LatLng): number {
    const dLat = toRad(b.lat - a.lat);
    const dLng = toRad(b.lng - a.lng);
    const h =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(a.lat)) *
            Math.cos(toRad(b.lat)) *
            Math.sin(dLng / 2) ** 2;

    return 2 * EARTH_RADIUS_M * Math.asin(Math.sqrt(h));
}

/** Compass bearing from a to b in degrees (0 = north, clockwise). */
export function bearing(a: LatLng, b: LatLng): number {
    const dLng = toRad(b.lng - a.lng);
    const y = Math.sin(dLng) * Math.cos(toRad(b.lat));
    const x =
        Math.cos(toRad(a.lat)) * Math.sin(toRad(b.lat)) -
        Math.sin(toRad(a.lat)) * Math.cos(toRad(b.lat)) * Math.cos(dLng);

    return (toDeg(Math.atan2(y, x)) + 360) % 360;
}

/** Average city speed used when no road route is available. */
const FALLBACK_SPEED_MPS = 6.9; // about 25 km/h

function straightLine(from: LatLng, to: LatLng): RoadRoute {
    const meters = distanceMeters(from, to);

    return {
        path: [from, to],
        distanceMeters: meters,
        durationSeconds: meters / FALLBACK_SPEED_MPS,
        isRoad: false,
    };
}

/**
 * The driving route between two points, or a straight line with an estimated time when the
 * router cannot be reached.
 */
export async function fetchRoute(
    from: LatLng,
    to: LatLng,
    signal?: AbortSignal,
): Promise<RoadRoute> {
    const url =
        `${ROUTING_URL}/route/v1/driving/${from.lng},${from.lat};${to.lng},${to.lat}` +
        '?overview=full&geometries=geojson';

    // A slow router must not freeze the ETA: give up after a few seconds and use the estimate.
    const timeout = new AbortController();
    let timedOut = false;
    const timer = setTimeout(() => {
        timedOut = true;
        timeout.abort();
    }, ROUTING_TIMEOUT_MS);
    const cancel = () => timeout.abort();
    signal?.addEventListener('abort', cancel);

    try {
        const response = await fetch(url, { signal: timeout.signal });
        if (!response.ok) return straightLine(from, to);

        const data = (await response.json()) as {
            routes?: {
                distance: number;
                duration: number;
                geometry: { coordinates: [number, number][] };
            }[];
        };
        const route = data.routes?.[0];
        if (!route || route.geometry.coordinates.length < 2) {
            return straightLine(from, to);
        }

        return {
            path: route.geometry.coordinates.map(([lng, lat]) => ({
                lat,
                lng,
            })),
            distanceMeters: route.distance,
            durationSeconds: route.duration,
            isRoad: true,
        };
    } catch (error) {
        if (
            !timedOut &&
            error instanceof DOMException &&
            error.name === 'AbortError'
        ) {
            throw error;
        }

        return straightLine(from, to);
    } finally {
        clearTimeout(timer);
        signal?.removeEventListener('abort', cancel);
    }
}

/** "1:54 pm" */
export function formatClock(date: Date): string {
    return date
        .toLocaleTimeString('en-PH', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        })
        .toLowerCase();
}

/** "3 min", "1 hr 5 min", or "Less than a minute". */
export function formatDuration(seconds: number): string {
    const minutes = Math.round(seconds / 60);
    if (minutes < 1) return 'Less than a minute';
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return rest === 0 ? `${hours} hr` : `${hours} hr ${rest} min`;
}
