import { reverseGeocode } from '@/lib/maps';
import { ref } from 'vue';

/**
 * Looks up the device's current address so a pickup order can be pre-filled.
 * The caller decides when to apply it, so the user's own edits are never overwritten.
 */
export function usePickupAddressAutofill() {
    const locating = ref(false);
    const failed = ref(false);

    function detectAddress(): Promise<string | null> {
        failed.value = false;

        if (!('geolocation' in navigator)) {
            failed.value = true;
            return Promise.resolve(null);
        }

        locating.value = true;

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        resolve(
                            await reverseGeocode(
                                position.coords.latitude,
                                position.coords.longitude,
                            ),
                        );
                    } catch {
                        failed.value = true;
                        resolve(null);
                    } finally {
                        locating.value = false;
                    }
                },
                () => {
                    locating.value = false;
                    failed.value = true;
                    resolve(null);
                },
                { enableHighAccuracy: true, timeout: 10_000 },
            );
        });
    }

    return { locating, failed, detectAddress };
}
