import type { LatLng } from '@/lib/maps';
import {
    fetchNavRoute,
    navProgress,
    spokenInstruction,
    type NavProgress,
    type NavRoute,
} from '@/lib/navigation';
import { bearing, distanceMeters } from '@/lib/routing';
import { computed, onBeforeUnmount, ref, watch, type Ref } from 'vue';

/** Re-route only after this many GPS fixes in a row are off the route, so one bad fix does not. */
const OFF_ROUTE_FIXES = 2;
/** Never ask for a new route more often than this. */
const MIN_REROUTE_MS = 10_000;
/** When routing failed, try again this often. */
const RETRY_MS = 15_000;
/** The early voice prompt is given this far before a turn, the final one this close. */
const EARLY_PROMPT_METERS = 250;
const FINAL_PROMPT_METERS = 60;
/** A rider has to move this far before the map turns to a new direction (GPS jitters when stopped). */
const HEADING_MIN_MOVE_METERS = 5;

const MUTE_KEY = 'driver-nav-muted';

export type NavStatus =
    | 'locating'
    | 'routing'
    | 'navigating'
    | 'rerouting'
    | 'arrived'
    | 'unavailable';

/**
 * Phones (iOS especially) only allow speech that starts from a tap. Call this inside the tap that
 * opens navigation so the later, automatic prompts are allowed to play.
 */
export function primeSpeech() {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) return;

    const silent = new SpeechSynthesisUtterance(' ');
    silent.volume = 0;
    window.speechSynthesis.speak(silent);
}

function readMuted(): boolean {
    try {
        return localStorage.getItem(MUTE_KEY) === '1';
    } catch {
        return false;
    }
}

/**
 * Turn-by-turn guidance from the rider's live position to a stop: the route, the next turn, the
 * remaining time and distance, re-routing when the rider leaves the route, arrival, and spoken
 * prompts through the phone's own speech engine.
 */
export function useNavigation(
    position: Ref<LatLng | null>,
    destination: Ref<LatLng | null>,
    active: Ref<boolean>,
) {
    const route = ref<NavRoute | null>(null);
    const progress = ref<NavProgress | null>(null);
    const status = ref<NavStatus>('locating');
    const heading = ref<number | null>(null);
    const muted = ref(readMuted());

    let abort: AbortController | null = null;
    let lastFetchAt = 0;
    let offRouteFixes = 0;
    let lastHeadingPoint: LatLng | null = null;
    let routeVersion = 0;
    const spoken = new Set<string>();

    const canSpeak =
        typeof window !== 'undefined' && 'speechSynthesis' in window;

    function say(text: string) {
        if (muted.value || !canSpeak) return;

        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 1;
        window.speechSynthesis.speak(utterance);
    }

    function sayOnce(key: string, text: string) {
        const fullKey = `${routeVersion}:${key}`;
        if (spoken.has(fullKey)) return;
        spoken.add(fullKey);
        say(text);
    }

    function toggleMute() {
        muted.value = !muted.value;
        if (muted.value && canSpeak) window.speechSynthesis.cancel();
        try {
            localStorage.setItem(MUTE_KEY, muted.value ? '1' : '0');
        } catch {
            // The choice still holds for this trip.
        }
    }

    async function requestRoute(rerouting: boolean) {
        const from = position.value;
        const to = destination.value;
        if (!from || !to) return;

        abort?.abort();
        abort = new AbortController();
        const mine = abort;
        lastFetchAt = Date.now();
        status.value = rerouting ? 'rerouting' : 'routing';
        if (rerouting) say('Rerouting');

        const fetched = await fetchNavRoute(from, to, mine.signal);
        if (mine.signal.aborted || !active.value) return;

        if (!fetched) {
            // Keep the old route on screen if there is one; otherwise say routing is unavailable.
            status.value = route.value ? 'navigating' : 'unavailable';
            return;
        }

        route.value = fetched;
        routeVersion++;
        offRouteFixes = 0;
        status.value = 'navigating';
        update();

        if (status.value === 'navigating' && !rerouting) {
            sayOnce('depart', fetched.steps[0].instruction);
        }
    }

    function updateHeading(current: LatLng) {
        if (!lastHeadingPoint) {
            lastHeadingPoint = current;
            return;
        }
        if (
            distanceMeters(lastHeadingPoint, current) < HEADING_MIN_MOVE_METERS
        ) {
            return;
        }
        heading.value = bearing(lastHeadingPoint, current);
        lastHeadingPoint = current;
    }

    function announce(p: NavProgress) {
        const next = p.nextStep;
        if (!next) return;

        const key = `step-${p.stepIndex}`;
        if (p.distanceToManeuver <= FINAL_PROMPT_METERS) {
            sayOnce(`${key}-final`, next.instruction);
        } else if (p.distanceToManeuver <= EARLY_PROMPT_METERS) {
            sayOnce(
                `${key}-early`,
                spokenInstruction(next, p.distanceToManeuver),
            );
        }
    }

    function update() {
        if (!active.value) return;

        const here = position.value;
        const to = destination.value;
        if (!here || !to) {
            status.value = 'locating';
            return;
        }

        updateHeading(here);

        if (!route.value) {
            const waitedEnough =
                Date.now() - lastFetchAt >=
                (status.value === 'unavailable' ? RETRY_MS : 0);
            if (status.value !== 'routing' && waitedEnough) {
                void requestRoute(false);
            }
            return;
        }

        const p = navProgress(route.value, here, to);
        progress.value = p;

        if (p.arrived) {
            if (status.value !== 'arrived') {
                status.value = 'arrived';
                sayOnce('arrived', 'You have arrived at your destination');
            }
            return;
        }

        offRouteFixes = p.offRoute ? offRouteFixes + 1 : 0;
        if (
            offRouteFixes >= OFF_ROUTE_FIXES &&
            Date.now() - lastFetchAt >= MIN_REROUTE_MS &&
            status.value !== 'rerouting'
        ) {
            void requestRoute(true);
            return;
        }

        if (status.value !== 'rerouting') status.value = 'navigating';
        announce(p);
    }

    function reset() {
        abort?.abort();
        abort = null;
        route.value = null;
        progress.value = null;
        status.value = 'locating';
        lastFetchAt = 0;
        offRouteFixes = 0;
        spoken.clear();
        if (canSpeak) window.speechSynthesis.cancel();
    }

    watch(() => [position.value?.lat, position.value?.lng], update);

    // A new stop (the next leg of the run) starts a fresh route.
    watch(
        () => [destination.value?.lat, destination.value?.lng],
        () => {
            reset();
            update();
        },
    );

    watch(active, (on) => {
        if (on) {
            reset();
            update();
        } else {
            reset();
        }
    });

    // Routing failed: keep trying while the rider waits, even without a new GPS fix.
    const retry = setInterval(() => {
        if (active.value && status.value === 'unavailable') update();
    }, RETRY_MS);

    onBeforeUnmount(() => {
        clearInterval(retry);
        reset();
    });

    // Start right away when the rider's position is already known.
    update();

    const currentStep = computed(() =>
        route.value && progress.value
            ? route.value.steps[progress.value.stepIndex]
            : null,
    );

    return {
        route,
        progress,
        status,
        heading,
        muted,
        canSpeak,
        currentStep,
        toggleMute,
        recalculate: () => requestRoute(true),
    };
}
