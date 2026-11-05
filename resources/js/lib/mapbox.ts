import type { LatLng } from '@/lib/maps';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

/**
 * Shared Mapbox GL setup. The public (pk.) token is the only credential the browser ever sees;
 * geocoding and shop-scoped routing go through the Laravel proxy.
 */
// The server puts the public token in the page, so the committed build never contains it;
// a token set at build time still works as a fallback.
export const MAPBOX_TOKEN = (document
    .querySelector('meta[name="mapbox-token"]')
    ?.getAttribute('content') ||
    import.meta.env.VITE_MAPBOX_TOKEN ||
    '') as string;
export const hasMapboxToken = MAPBOX_TOKEN !== '';

mapboxgl.accessToken = MAPBOX_TOKEN;

export const STANDARD_STYLE = 'mapbox://styles/mapbox/standard';

export type LightPreset = 'day' | 'dusk' | 'night';
export const LIGHT_PRESETS: LightPreset[] = ['day', 'dusk', 'night'];

/** Cavite province centre, used when nothing better is known. */
export const DEFAULT_CENTER: LatLng = { lat: 14.2456, lng: 120.8687 };

export type MapOptions = {
    center?: LatLng | null;
    zoom?: number;
    pitch?: number;
    bearing?: number;
    lightPreset?: LightPreset;
    interactive?: boolean;
};

export const toLngLat = (point: LatLng): [number, number] => [
    point.lng,
    point.lat,
];

/** A Mapbox Standard map: 3D buildings, labels on, tilted view. */
export function createStandardMap(
    container: HTMLElement,
    options: MapOptions = {},
): mapboxgl.Map {
    const map = new mapboxgl.Map({
        container,
        style: STANDARD_STYLE,
        center: toLngLat(options.center ?? DEFAULT_CENTER),
        zoom: options.zoom ?? 16,
        pitch: options.pitch ?? 60,
        bearing: options.bearing ?? -45,
        interactive: options.interactive ?? true,
        attributionControl: false,
        config: {
            basemap: { lightPreset: options.lightPreset ?? 'day' },
        },
    });

    map.addControl(
        new mapboxgl.AttributionControl({ compact: true }),
        'bottom-left',
    );

    map.on('style.load', () => applyStandardConfig(map, options.lightPreset));

    return map;
}

function applyStandardConfig(map: mapboxgl.Map, preset?: LightPreset) {
    map.setConfigProperty('basemap', 'show3dObjects', true);
    map.setConfigProperty('basemap', 'showPointOfInterestLabels', true);
    map.setConfigProperty('basemap', 'showPlaceLabels', true);
    map.setConfigProperty('basemap', 'showTransitLabels', true);
    map.setConfigProperty('basemap', 'lightPreset', preset ?? 'day');
}

export function setLightPreset(map: mapboxgl.Map, preset: LightPreset) {
    map.setConfigProperty('basemap', 'lightPreset', preset);
}

/** Runs `run` once the style is ready, immediately if it already is. */
export function whenStyleReady(map: mapboxgl.Map, run: () => void) {
    if (map.isStyleLoaded()) run();
    else map.once('style.load', run);
}

// ─── Route lines ─────────────────────────────────────────────────────────────

type LineString = { type: 'LineString'; coordinates: [number, number][] };

type LineFeature = {
    type: 'Feature';
    properties: Record<string, never>;
    geometry: LineString;
};

const emptyLine = (): LineFeature => ({
    type: 'Feature',
    properties: {},
    geometry: { type: 'LineString', coordinates: [] },
});

/**
 * Draws (or clears) a route line with a white casing. `slot: 'middle'` keeps it under labels and
 * above the roads in the Standard style.
 */
export function drawRouteLine(
    map: mapboxgl.Map,
    id: string,
    coordinates: [number, number][] | null,
    options: { color?: string; dashed?: boolean } = {},
) {
    whenStyleReady(map, () => {
        const data: LineFeature = coordinates
            ? {
                  type: 'Feature',
                  properties: {},
                  geometry: { type: 'LineString', coordinates },
              }
            : emptyLine();

        const source = map.getSource(id) as mapboxgl.GeoJSONSource | undefined;

        if (source) {
            source.setData(data);
        } else {
            map.addSource(id, { type: 'geojson', data });
            map.addLayer({
                id: `${id}-casing`,
                type: 'line',
                source: id,
                slot: 'middle',
                layout: { 'line-cap': 'round', 'line-join': 'round' },
                paint: {
                    'line-color': '#ffffff',
                    'line-width': 10,
                    'line-opacity': 0.95,
                },
            });
            map.addLayer({
                id: `${id}-line`,
                type: 'line',
                source: id,
                slot: 'middle',
                layout: { 'line-cap': 'round', 'line-join': 'round' },
                paint: {
                    'line-color': options.color ?? '#10b981',
                    'line-width': 5,
                },
            });
        }

        if (map.getLayer(`${id}-line`)) {
            map.setPaintProperty(
                `${id}-line`,
                'line-color',
                options.color ?? '#10b981',
            );
            map.setPaintProperty(
                `${id}-line`,
                'line-dasharray',
                options.dashed ? [1, 2] : [1, 0],
            );
        }
    });
}

export function lineCoordinates(
    geometry: LineString | { coordinates: number[][] },
): [number, number][] {
    return geometry.coordinates as [number, number][];
}

export function fitPoints(
    map: mapboxgl.Map,
    points: LatLng[],
    options: { padding?: number; maxZoom?: number; animate?: boolean } = {},
) {
    if (points.length === 0) return;

    if (points.length === 1) {
        map.easeTo({
            center: toLngLat(points[0]),
            zoom: options.maxZoom ?? 16,
            duration: options.animate === false ? 0 : 800,
        });
        return;
    }

    const bounds = new mapboxgl.LngLatBounds();
    points.forEach((p) => bounds.extend(toLngLat(p)));
    map.fitBounds(bounds, {
        padding: options.padding ?? 70,
        maxZoom: options.maxZoom ?? 17,
        duration: options.animate === false ? 0 : 800,
    });
}

// ─── Markers (plain DOM elements) ────────────────────────────────────────────

export function dotElement(color: string, size = 18): HTMLElement {
    const el = document.createElement('span');
    el.style.cssText = `display:block;width:${size}px;height:${size}px;border-radius:9999px;background:${color};border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.45)`;
    return el;
}

/** A teardrop pin whose tip sits on the coordinate (use anchor "bottom"). */
export function pinElement(color = '#2563eb'): HTMLElement {
    const wrap = document.createElement('span');
    wrap.style.cssText = 'display:block;width:26px;height:34px;cursor:grab';
    wrap.innerHTML = `<span style="display:block;margin:0 auto;width:26px;height:26px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:${color};border:3px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.5)"></span>`;
    return wrap;
}

/** A numbered stop for optimised routes. */
export function numberElement(n: number, color = '#2563eb'): HTMLElement {
    const el = document.createElement('span');
    el.textContent = String(n);
    el.style.cssText = `display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9999px;background:${color};color:#fff;font:700 13px system-ui,sans-serif;border:3px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.45)`;
    return el;
}

export function addMarker(
    map: mapboxgl.Map,
    element: HTMLElement,
    point: LatLng,
    options: { anchor?: mapboxgl.Anchor; draggable?: boolean } = {},
): mapboxgl.Marker {
    return new mapboxgl.Marker({
        element,
        anchor: options.anchor ?? 'center',
        draggable: options.draggable ?? false,
    })
        .setLngLat(toLngLat(point))
        .addTo(map);
}

/**
 * Glides a marker to a new position instead of jumping, so a vehicle keeps moving between GPS
 * updates. Returns a function that cancels the animation.
 */
export function glideMarker(
    marker: mapboxgl.Marker,
    to: LatLng,
    durationMs: number,
    onFrame?: (position: LatLng) => void,
): () => void {
    const start = marker.getLngLat();
    const startedAt = performance.now();
    let frame = 0;

    const step = (now: number) => {
        const t = Math.min(1, (now - startedAt) / durationMs);
        const eased = 1 - (1 - t) ** 2;
        const position = {
            lat: start.lat + (to.lat - start.lat) * eased,
            lng: start.lng + (to.lng - start.lng) * eased,
        };
        marker.setLngLat(toLngLat(position));
        onFrame?.(position);
        if (t < 1) frame = requestAnimationFrame(step);
    };

    frame = requestAnimationFrame(step);

    return () => cancelAnimationFrame(frame);
}

export { mapboxgl };
