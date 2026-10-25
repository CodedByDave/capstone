<script setup lang="ts">
import DirectionsMap from '@/components/driver/DirectionsMap.vue';
import DriverLayout from '@/components/driver/DriverLayout.vue';
import { useDriverLocation } from '@/composables/useDriverLocation';
import { googleMapsNavigationUrl, type GoogleRoute } from '@/lib/googleMaps';
import type { DriverInfo, DriverJob } from '@/types/driver';
import { Link } from '@inertiajs/vue3';
import { Navigation } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    driver: DriverInfo;
    job: DriverJob | null;
    maps_key: string | null;
}>();

const jobId = computed(() => props.job?.public_id ?? null);
const sharing = computed(() => props.job !== null && props.driver.is_online);
const { position, error, start } = useDriverLocation(jobId, sharing);
onMounted(start);

const route = ref<GoogleRoute | null>(null);
</script>

<template>
    <DriverLayout title="Map">
        <h1 class="text-xl font-bold">Map</h1>

        <div
            v-if="!job"
            class="mt-4 rounded-2xl border border-dashed bg-white p-8 text-center text-sm text-slate-500"
        >
            No active stop. Your route appears here when an order is assigned.
        </div>

        <template v-else>
            <div class="mt-4 overflow-hidden rounded-2xl">
                <DirectionsMap
                    :api-key="maps_key"
                    :origin="position"
                    :destination="job.target"
                    height-class="h-[55vh]"
                    @route="route = $event"
                />
            </div>
            <p v-if="error" class="mt-2 text-xs text-amber-800">{{ error }}</p>

            <div class="mt-3 rounded-2xl bg-white p-4">
                <p class="text-xs text-slate-500">
                    Next stop · {{ job.type }}
                    <span v-if="route"
                        >· {{ route.distanceText }},
                        {{ route.durationText }}</span
                    >
                </p>
                <p class="mt-1 font-bold">{{ job.target.label }}</p>
                <p class="text-sm text-slate-500">{{ job.target.address }}</p>

                <div class="mt-3 flex gap-2">
                    <Link
                        :href="`/driver/jobs/${job.public_id}`"
                        class="flex h-11 flex-1 items-center justify-center rounded-xl bg-blue-600 font-semibold text-white"
                    >
                        Open job
                    </Link>
                    <a
                        :href="
                            googleMapsNavigationUrl(
                                job.target,
                                job.target.kind === 'customer'
                                    ? job.shop_stop
                                    : job.customer_stop,
                            )
                        "
                        target="_blank"
                        rel="noopener"
                        class="flex h-11 items-center gap-2 rounded-xl border px-4 font-semibold"
                    >
                        <Navigation class="h-4 w-4" aria-hidden="true" />
                        Navigate
                    </a>
                </div>
            </div>
        </template>
    </DriverLayout>
</template>
