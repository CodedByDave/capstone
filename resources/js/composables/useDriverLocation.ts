import type { LatLng } from '@/lib/maps';
import axios from 'axios';
import { onBeforeUnmount, ref, watch, type Ref } from 'vue';

/** How often the driver's position is sent to the server while a run is on the road. */
const SEND_EVERY_MS = 10_000;

/**
 * Follows the driver's GPS position. While `sharing` is true the position is also posted to the
 * run so the customer can follow it live.
 */
export function useDriverLocation(
    jobId: Ref<string | null>,
    sharing: Ref<boolean>,
) {
    const position = ref<LatLng | null>(null);
    const error = ref<string | null>(null);

    let watchId: number | null = null;
    let lastSent = 0;

    function send(latest: LatLng) {
        if (!sharing.value || !jobId.value) return;
        if (Date.now() - lastSent < SEND_EVERY_MS) return;

        lastSent = Date.now();
        axios.post(`/driver/jobs/${jobId.value}/location`, latest).catch(() => {
            // The next fix retries; a missed update is not worth interrupting the driver.
        });
    }

    function start() {
        if (watchId !== null) return;

        if (!('geolocation' in navigator)) {
            error.value = 'This device cannot share its location.';
            return;
        }

        watchId = navigator.geolocation.watchPosition(
            (fix) => {
                error.value = null;
                position.value = {
                    lat: fix.coords.latitude,
                    lng: fix.coords.longitude,
                };
                send(position.value);
            },
            () => {
                error.value = 'Turn on location access to get directions.';
            },
            { enableHighAccuracy: true, maximumAge: 5_000, timeout: 20_000 },
        );
    }

    function stop() {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }

    watch(jobId, () => {
        lastSent = 0;
    });

    onBeforeUnmount(stop);

    return { position, error, start, stop };
}
