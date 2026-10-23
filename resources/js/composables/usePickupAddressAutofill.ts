import { reverseGeocode } from '@/lib/maps';
import { ref } from 'vue';

export interface DetectedLocation {
    address: string | null;
    lat: number;
    lng: number;
}

/**
 * Looks up the device's current address so a pickup order can be pre-filled.
 * The caller decides when to apply it, so the user's own edits are never overwritten.
 */
export function usePickupAddressAutofill() {
    const locating = ref(false);
    const failed = ref(false);

    /** The device's position and, when it can be looked up, its address. */
    function detectLocation(): Promise<DetectedLocation | null> {
        failed.value = false;

        if (!('geolocation' in navigator)) {
            failed.value = true;
            return Promise.resolve(null);
        }

        locating.value = true;

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const { latitude: lat, longitude: lng } = position.coords;

                    try {
                        resolve({
                            address: await reverseGeocode(lat, lng),
                            lat,
                            lng,
                        });
                    } catch {
                        // The position is still useful even without an address.
                        failed.value = true;
                        resolve({ address: null, lat, lng });
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

    async function detectAddress(): Promise<string | null> {
        return (await detectLocation())?.address ?? null;
    }

    return { locating, failed, detectAddress, detectLocation };
}
