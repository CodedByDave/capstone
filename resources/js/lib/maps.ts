import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

export type LatLng = { lat: number; lng: number };

// Cavite province center, the same default view used on shop registration.
export const DEFAULT_MAP_CENTER: L.LatLngTuple = [14.2456, 120.8687];

export function createBaseMap(
    el: HTMLElement,
    center: L.LatLngExpression = DEFAULT_MAP_CENTER,
    zoom = 13,
): L.Map {
    const map = L.map(el, { scrollWheelZoom: false }).setView(center, zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    return map;
}

/**
 * A CSS-only marker. Avoids Leaflet's default PNG marker, whose image URLs break
 * when bundled unless they are re-imported on every page.
 */
export function dotIcon(color: string, size = 18): L.DivIcon {
    return L.divIcon({
        className: '',
        html: `<span style="display:block;width:${size}px;height:${size}px;border-radius:9999px;background:${color};border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.45)"></span>`,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
}

/** A larger draggable pin for choosing a location. */
export function pinIcon(color = '#2563eb'): L.DivIcon {
    return L.divIcon({
        className: '',
        html: `<span style="display:block;width:26px;height:26px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:${color};border:3px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.5)"></span>`,
        iconSize: [26, 26],
        iconAnchor: [13, 26],
    });
}

// ─── Nominatim (OpenStreetMap geocoding) ──────────────────────────────────────
// Usage policy: at most one request per second, no bulk/auto-complete hammering,
// results cached by the browser. Callers should also debounce user input.

const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';
const MIN_INTERVAL_MS = 1100;
let lastRequestAt = 0;
let queue: Promise<unknown> = Promise.resolve();

function throttled<T>(task: () => Promise<T>): Promise<T> {
    const run = queue.then(async () => {
        const wait = lastRequestAt + MIN_INTERVAL_MS - Date.now();
        if (wait > 0) {
            await new Promise((resolve) => setTimeout(resolve, wait));
        }
        lastRequestAt = Date.now();
        return task();
    });
    queue = run.catch(() => undefined);
    return run;
}

export interface PlaceResult {
    place_id: number;
    display_name: string;
    lat: string;
    lon: string;
}

export function searchPlaces(query: string): Promise<PlaceResult[]> {
    return throttled(async () => {
        const params = new URLSearchParams({
            q: query,
            format: 'json',
            limit: '5',
            countrycodes: 'ph',
        });
        const res = await fetch(`${NOMINATIM_URL}/search?${params}`, {
            headers: { 'Accept-Language': 'en' },
        });
        if (!res.ok) throw new Error(`Address search failed (${res.status})`);
        return (await res.json()) as PlaceResult[];
    });
}

export function reverseGeocode(
    lat: number,
    lng: number,
): Promise<string | null> {
    return throttled(async () => {
        const params = new URLSearchParams({
            lat: String(lat),
            lon: String(lng),
            format: 'json',
            zoom: '18',
        });
        const res = await fetch(`${NOMINATIM_URL}/reverse?${params}`, {
            headers: { 'Accept-Language': 'en' },
        });
        if (!res.ok) throw new Error(`Address lookup failed (${res.status})`);
        const data = (await res.json()) as { display_name?: string };
        return data.display_name ?? null;
    });
}

export function formatPeso(value: number): string {
    return `₱${value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}
