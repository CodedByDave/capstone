import type { LatLng } from '@/lib/maps';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/**
 * The look of the live tracking map: soft, light tiles, no zoom buttons, a white vehicle with a
 * gentle shadow, and a pulsing green stop, in the style of ride-hailing apps.
 */

export type TrackingBaseLayer = 'map' | 'satellite';

const BASE_LAYERS: Record<
    TrackingBaseLayer,
    { url: string; attribution: string }
> = {
    map: {
        url: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
        attribution: '© OpenStreetMap contributors © CARTO',
    },
    satellite: {
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        attribution: 'Tiles © Esri',
    },
};

export function createTrackingMap(el: HTMLElement): {
    map: L.Map;
    setBaseLayer: (kind: TrackingBaseLayer) => void;
} {
    const map = L.map(el, {
        zoomControl: false,
        attributionControl: false,
        zoomSnap: 0.5,
    }).setView([14.2456, 120.8687], 14);

    L.control.attribution({ position: 'bottomleft', prefix: false }).addTo(map);

    let current: L.TileLayer | null = null;

    const setBaseLayer = (kind: TrackingBaseLayer) => {
        current?.remove();
        const source = BASE_LAYERS[kind];
        current = L.tileLayer(source.url, {
            attribution: source.attribution,
            maxZoom: 19,
            subdomains: 'abcd',
        }).addTo(map);
        current.bringToBack();
    };

    setBaseLayer('map');

    return { map, setBaseLayer };
}

// ─── Vehicles ────────────────────────────────────────────────────────────────

const CAR_SVG = `
<svg viewBox="0 0 40 72" width="34" height="61" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <ellipse cx="20" cy="40" rx="17" ry="30" fill="rgba(0,0,0,.18)"/>
  <rect x="5" y="4" width="30" height="62" rx="14" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5"/>
  <rect x="9" y="14" width="22" height="13" rx="5" fill="#1f2937"/>
  <rect x="11" y="30" width="18" height="16" rx="4" fill="#f3f4f6" stroke="#e5e7eb"/>
  <rect x="9" y="49" width="22" height="9" rx="4" fill="#1f2937"/>
  <rect x="3" y="22" width="3" height="7" rx="1.5" fill="#9ca3af"/>
  <rect x="34" y="22" width="3" height="7" rx="1.5" fill="#9ca3af"/>
</svg>`;

const MOTORCYCLE_SVG = `
<svg viewBox="0 0 36 72" width="28" height="56" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <ellipse cx="18" cy="40" rx="13" ry="30" fill="rgba(0,0,0,.18)"/>
  <rect x="14.5" y="3" width="7" height="14" rx="3.5" fill="#374151"/>
  <rect x="14" y="54" width="8" height="15" rx="4" fill="#374151"/>
  <rect x="3" y="17" width="30" height="4.5" rx="2.25" fill="#4b5563"/>
  <rect x="9" y="17" width="18" height="40" rx="9" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5"/>
  <ellipse cx="18" cy="35" rx="7.5" ry="9" fill="#e5e7eb"/>
  <circle cx="18" cy="28" r="6.5" fill="#111827"/>
  <circle cx="18" cy="28" r="3.2" fill="#6b7280"/>
</svg>`;

export type VehicleKind = 'car' | 'motorcycle';

/** The vehicle on the map. Its inner element is rotated to face the way it is travelling. */
export function vehicleIcon(kind: VehicleKind): L.DivIcon {
    const svg = kind === 'car' ? CAR_SVG : MOTORCYCLE_SVG;
    const [w, h] = kind === 'car' ? [34, 61] : [28, 56];

    return L.divIcon({
        className: 'trk-vehicle',
        html: `<div class="trk-vehicle__body" style="width:${w}px;height:${h}px">${svg}</div>`,
        iconSize: [w, h],
        iconAnchor: [w / 2, h / 2],
    });
}

/** Turns the vehicle (0 = pointing north, clockwise). */
export function rotateVehicle(marker: L.Marker, degrees: number): void {
    const body = marker
        .getElement()
        ?.querySelector<HTMLElement>('.trk-vehicle__body');
    if (body) body.style.transform = `rotate(${degrees}deg)`;
}

/** The green stop the rider is heading to. */
export function stopIcon(): L.DivIcon {
    return L.divIcon({
        className: 'trk-stop',
        html: '<span class="trk-stop__pulse"></span><span class="trk-stop__dot"></span>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });
}

/** A small dark pin for the shop. */
export function shopIcon(): L.DivIcon {
    return L.divIcon({
        className: 'trk-shop',
        html: '<span class="trk-shop__pin"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg></span>',
        iconSize: [28, 28],
        iconAnchor: [14, 14],
    });
}

// ─── Smooth movement ─────────────────────────────────────────────────────────

/**
 * Glides a marker from where it is to a new position instead of jumping, so the vehicle keeps
 * moving between GPS updates. Returns a function that cancels the animation.
 */
export function glideMarker(
    marker: L.Marker,
    to: LatLng,
    durationMs: number,
    onFrame?: (position: LatLng) => void,
): () => void {
    const start = marker.getLatLng();
    const from = { lat: start.lat, lng: start.lng };
    const startedAt = performance.now();
    let frame = 0;

    const step = (now: number) => {
        const t = Math.min(1, (now - startedAt) / durationMs);
        // Ease out, so it slows slightly as it reaches the reported point.
        const eased = 1 - (1 - t) ** 2;
        const position = {
            lat: from.lat + (to.lat - from.lat) * eased,
            lng: from.lng + (to.lng - from.lng) * eased,
        };
        marker.setLatLng(position);
        onFrame?.(position);
        if (t < 1) frame = requestAnimationFrame(step);
    };

    frame = requestAnimationFrame(step);

    return () => cancelAnimationFrame(frame);
}
