/**
 * The look of the live tracking markers: a white vehicle with a gentle shadow, a pulsing green
 * stop and a dark shop pin, in the style of ride-hailing apps. Markers are plain DOM elements
 * for Mapbox GL; the styles live in LiveTrackingMap.vue.
 */

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
export function vehicleElement(kind: VehicleKind): HTMLElement {
    const svg = kind === 'car' ? CAR_SVG : MOTORCYCLE_SVG;
    const [w, h] = kind === 'car' ? [34, 61] : [28, 56];
    const el = document.createElement('div');
    el.className = 'trk-vehicle';
    el.innerHTML = `<div class="trk-vehicle__body" style="width:${w}px;height:${h}px">${svg}</div>`;
    return el;
}

/** Turns the vehicle (0 = pointing north, clockwise). */
export function rotateVehicle(element: HTMLElement, degrees: number): void {
    const body = element.querySelector<HTMLElement>('.trk-vehicle__body');
    if (body) body.style.transform = `rotate(${degrees}deg)`;
}

/** The green stop the rider is heading to. */
export function stopElement(): HTMLElement {
    const el = document.createElement('div');
    el.className = 'trk-stop';
    el.style.cssText = 'width:24px;height:24px';
    el.innerHTML =
        '<span class="trk-stop__pulse"></span><span class="trk-stop__dot"></span>';
    return el;
}

/** A small dark pin for the shop. */
export function shopElement(): HTMLElement {
    const el = document.createElement('div');
    el.className = 'trk-shop';
    el.innerHTML =
        '<span class="trk-shop__pin"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg></span>';
    return el;
}

/** The time chip above the stop, like "DROPOFF 1:54 pm". */
export function chipElement(label: string, time: string): HTMLElement {
    const el = document.createElement('div');
    el.className = 'trk-chip';
    const labelEl = document.createElement('span');
    labelEl.className = 'trk-chip__label';
    labelEl.textContent = label;
    el.append(labelEl);
    if (time) {
        const timeEl = document.createElement('span');
        timeEl.className = 'trk-chip__time';
        timeEl.textContent = time;
        el.append(timeEl);
    }
    return el;
}
