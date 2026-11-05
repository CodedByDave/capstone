<script setup lang="ts">
import DirectionsMap from '@/components/driver/DirectionsMap.vue';
import DriverLayout from '@/components/driver/DriverLayout.vue';
import JobWorkflow from '@/components/driver/JobWorkflow.vue';
import { useDriverLocation } from '@/composables/useDriverLocation';
import type { RouteSummary } from '@/lib/routing';
import type { DriverInfo, DriverJob } from '@/types/driver';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, MapPin, MessageSquare, Phone, User } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    driver: DriverInfo;
    job: DriverJob | null;
}>();

const jobId = computed(() => props.job?.public_id ?? null);
const sharing = computed(
    () => props.job?.next_status != null && props.driver.is_online,
);
const { position, error, start } = useDriverLocation(jobId, sharing);
onMounted(start);

const route = ref<RouteSummary | null>(null);

// A pickup run begins at the shop: the rider leaves the shop for the customer, so the route is
// drawn from the shop until the laundry has been picked up.
const routeOrigin = computed(() => {
    const shop = props.job?.shop_stop;

    return props.job?.type === 'pickup' &&
        props.job.status === 'assigned' &&
        shop?.lat != null &&
        shop?.lng != null
        ? { lat: shop.lat, lng: shop.lng }
        : position.value;
});
</script>

<template>
    <DriverLayout title="Map" flush>
        <div v-if="!job" class="px-5 pt-8 text-center text-sm text-slate-500">
            <h1 class="text-left text-2xl font-bold text-slate-900">Map</h1>
            <p class="mt-10 rounded-2xl border border-dashed p-8">
                No active stop. Your route appears here when an order is
                assigned.
            </p>
        </div>

        <template v-else>
            <div class="relative">
                <DirectionsMap
                    :origin="routeOrigin"
                    :destination="job.target"
                    height-class="h-[62vh]"
                    @route="route = $event"
                />
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 flex items-center gap-2 bg-gradient-to-b from-white/90 to-transparent px-5 pt-6 pb-6"
                >
                    <Link
                        href="/driver"
                        class="pointer-events-auto text-slate-800"
                        aria-label="Back to home"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <h1 class="text-2xl font-bold text-slate-900">Map</h1>
                </div>
            </div>

            <div class="relative -mt-6 rounded-t-3xl bg-white px-5 pt-6">
                <p v-if="error" class="mb-2 text-xs text-amber-800">
                    {{ error }}
                </p>

                <span
                    class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[10px] font-semibold text-[#8a6100] capitalize"
                >
                    {{ job.type }}
                </span>
                <span v-if="route" class="ml-2 text-[11px] text-slate-500">
                    {{ route.distanceText }} · {{ route.durationText }}
                </span>

                <p class="mt-3 flex items-center gap-2 text-base font-semibold">
                    <User class="h-4 w-4" aria-hidden="true" />
                    {{ job.target.label }}
                </p>
                <p
                    class="mt-1.5 flex items-start gap-2 text-[11px] text-slate-600"
                >
                    <MapPin
                        class="mt-0.5 h-3.5 w-3.5 shrink-0"
                        aria-hidden="true"
                    />
                    {{ job.target.address }}
                </p>

                <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-medium">
                    <span
                        v-if="job.service"
                        class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[#8a6100]"
                    >
                        {{ job.service }}
                    </span>
                    <span
                        v-if="job.order_number"
                        class="rounded-md bg-[#ffe9a8] px-2 py-0.5 text-[#8a6100]"
                    >
                        Order #{{ job.order_number }}
                    </span>
                </div>

                <p
                    v-if="job.target.kind === 'shop'"
                    class="mt-2 text-[11px] text-slate-500"
                >
                    Customer: {{ job.customer_name }}
                </p>

                <div class="mt-4 flex gap-2">
                    <a
                        v-if="job.customer_phone"
                        :href="`tel:${job.customer_phone}`"
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-200 text-slate-700"
                        aria-label="Call customer"
                    >
                        <Phone class="h-4 w-4" />
                    </a>
                    <a
                        v-if="job.customer_phone"
                        :href="`sms:${job.customer_phone}`"
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-200 text-slate-700"
                        aria-label="Message customer"
                    >
                        <MessageSquare class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-5 pb-4">
                    <JobWorkflow :job="job" />
                </div>
            </div>
        </template>
    </DriverLayout>
</template>
