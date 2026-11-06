import { MAPBOX_TOKEN } from '@/lib/mapbox';
import type { LatLng } from '@/lib/maps';
import { distanceMeters } from '@/lib/routing';

/**
 * In-app turn-by-turn navigation for riders. The route and its steps come from Mapbox Directions
 * (the same service the maps already use); everything else (where the rider is on the route, the
 * next turn, re-routing and arrival) is worked out here from the phone's GPS.
 */

const DIRECTIONS_URL = 'https://api.mapbox.com/directions/v5/mapbox/driving';
const TIMEOUT_MS = 8_000;

/** Further than this from the route line, the rider has left the route. */
export const OFF_ROUTE_METERS = 40;
/** Closer than this to the destination, the rider has arrived. */
export const ARRIVED_METERS = 30;

export interface NavStep {
    /** "Turn left onto Aguinaldo Highway" */
    instruction: string;
    /** Mapbox maneuver type: depart, turn, roundabout, arrive, ... */
    type: string;
    /** left, slight right, uturn, straight, ... */
    modifier: string | null;
    /** The road the step follows. */
    name: string;
    /** Length of this step in meters. */
    distance: number;
    /** Where the maneuver happens. */
    location: LatLng;
}

export interface NavRoute {
    path: LatLng[];
    steps: NavStep[];
    distanceMeters: number;
    durationSeconds: number;
}

export interface NavProgress {
    /** The step the rider is driving along now. */
    stepIndex: number;
    /** The maneuver the rider makes next (the end of the current step). */
    nextStep: NavStep | null;
    /** Meters to that maneuver. */
    distanceToManeuver: number;
    remainingMeters: number;
    remainingSeconds: number;
    /** How far the rider is from the route line. */
    distanceFromRoute: number;
    offRoute: boolean;
    arrived: boolean;
}

interface MapboxStep {
    distance: number;
    name: string;
    maneuver: {
        instruction: string;
        type: string;
        modifier?: string;
        location: [number, number];
    };
}

/** The driving route from the rider to the stop, with its turn-by-turn steps. Null when routing is unavailable. */
export async function fetchNavRoute(
    from: LatLng,
    to: LatLng,
    signal?: AbortSignal,
): Promise<NavRoute | null> {
    if (!MAPBOX_TOKEN) return null;

    const url =
        `${DIRECTIONS_URL}/${from.lng},${from.lat};${to.lng},${to.lat}` +
        `?overview=full&geometries=geojson&steps=true&language=en` +
        `&access_token=${encodeURIComponent(MAPBOX_TOKEN)}`;

    const timeout = new AbortController();
    const timer = setTimeout(() => timeout.abort(), TIMEOUT_MS);
    const cancel = () => timeout.abort();
    signal?.addEventListener('abort', cancel);

    try {
        const response = await fetch(url, { signal: timeout.signal });
        if (!response.ok) return null;

        const data = (await response.json()) as {
            routes?: {
                distance: number;
                duration: number;
                geometry: { coordinates: [number, number][] };
                legs: { steps: MapboxStep[] }[];
            }[];
        };

        return parseNavRoute(data.routes?.[0]);
    } catch {
        return null;
    } finally {
        clearTimeout(timer);
        signal?.removeEventListener('abort', cancel);
    }
}

/** Turns a Mapbox route into the shape the navigation screen uses. */
export function parseNavRoute(
    route:
        | {
              distance: number;
              duration: number;
              geometry: { coordinates: [number, number][] };
              legs: { steps: MapboxStep[] }[];
          }
        | undefined,
): NavRoute | null {
    if (!route || route.geometry.coordinates.length < 2) return null;

    const steps = route.legs
        .flatMap((leg) => leg.steps)
        .map((step) => ({
            instruction: step.maneuver.instruction,
            type: step.maneuver.type,
            modifier: step.maneuver.modifier ?? null,
            name: step.name,
            distance: step.distance,
            location: {
                lat: step.maneuver.location[1],
                lng: step.maneuver.location[0],
            },
        }));

    if (steps.length === 0) return null;

    return {
        path: route.geometry.coordinates.map(([lng, lat]) => ({ lat, lng })),
        steps,
        distanceMeters: route.distance,
        durationSeconds: route.duration,
    };
}

/** Flat x/y meters around a reference point; accurate enough over the few hundred meters of one segment. */
function toLocalMeters(
    origin: LatLng,
    point: LatLng,
): { x: number; y: number } {
    const metersPerDegLat = 111_320;
    const metersPerDegLng = 111_320 * Math.cos((origin.lat * Math.PI) / 180);

    return {
        x: (point.lng - origin.lng) * metersPerDegLng,
        y: (point.lat - origin.lat) * metersPerDegLat,
    };
}

/**
 * Where the rider is along the route line: how far along it (meters from the start) and how far
 * off it. Uses the closest point on any segment of the line.
 */
export function locateOnPath(
    path: LatLng[],
    position: LatLng,
): { along: number; offset: number } {
    let best = { along: 0, offset: Infinity };
    let travelled = 0;

    for (let i = 0; i < path.length - 1; i++) {
        const a = path[i];
        const b = toLocalMeters(a, path[i + 1]);
        const p = toLocalMeters(a, position);
        const lengthSq = b.x * b.x + b.y * b.y;
        const segment = Math.sqrt(lengthSq);
        const t =
            lengthSq === 0
                ? 0
                : Math.max(0, Math.min(1, (p.x * b.x + p.y * b.y) / lengthSq));
        const offset = Math.hypot(p.x - b.x * t, p.y - b.y * t);

        if (offset < best.offset) {
            best = { along: travelled + segment * t, offset };
        }

        travelled += segment;
    }

    return best;
}

/** Total length of a line in meters, measured the same way as locateOnPath. */
function pathLength(path: LatLng[]): number {
    let total = 0;
    for (let i = 0; i < path.length - 1; i++) {
        const b = toLocalMeters(path[i], path[i + 1]);
        total += Math.hypot(b.x, b.y);
    }
    return total;
}

/** The rider's progress on the route: current step, distance to the next turn, and what is left. */
export function navProgress(
    route: NavRoute,
    position: LatLng,
    destination: LatLng,
): NavProgress {
    const { along, offset } = locateOnPath(route.path, position);
    const lineLength = pathLength(route.path) || route.distanceMeters;
    // Step distances come from Mapbox; scale them onto the line so both use the same meters.
    const scale =
        route.distanceMeters > 0 ? lineLength / route.distanceMeters : 1;

    let stepStart = 0;
    let stepIndex = route.steps.length - 1;
    for (let i = 0; i < route.steps.length; i++) {
        const stepEnd = stepStart + route.steps[i].distance * scale;
        if (along < stepEnd || i === route.steps.length - 1) {
            stepIndex = i;
            break;
        }
        stepStart = stepEnd;
    }

    const stepEnd = stepStart + route.steps[stepIndex].distance * scale;
    const remainingMeters = Math.max(0, lineLength - along);
    const toDestination = distanceMeters(position, destination);

    return {
        stepIndex,
        nextStep: route.steps[stepIndex + 1] ?? null,
        distanceToManeuver: Math.max(0, stepEnd - along),
        remainingMeters,
        remainingSeconds:
            lineLength > 0
                ? route.durationSeconds * (remainingMeters / lineLength)
                : 0,
        distanceFromRoute: offset,
        offRoute: offset > OFF_ROUTE_METERS,
        arrived: toDestination <= ARRIVED_METERS,
    };
}

/** Distance for the turn banner and voice: "200 m", "1.2 km". Rounded the way drivers read it. */
export function formatManeuverDistance(meters: number): string {
    if (meters >= 1000) return `${(meters / 1000).toFixed(1)} km`;
    if (meters >= 100) return `${Math.round(meters / 50) * 50} m`;

    return `${Math.max(0, Math.round(meters / 10) * 10)} m`;
}

/** What the voice says ahead of a maneuver: "In 200 meters, turn left onto Aguinaldo Highway". */
export function spokenInstruction(
    step: NavStep,
    meters: number | null,
): string {
    if (meters === null) return step.instruction;

    const distance =
        meters >= 1000
            ? `${(meters / 1000).toFixed(1)} kilometers`
            : `${Math.round(meters / 50) * 50 || 50} meters`;
    const instruction =
        step.instruction.charAt(0).toLowerCase() + step.instruction.slice(1);

    return `In ${distance}, ${instruction}`;
}
