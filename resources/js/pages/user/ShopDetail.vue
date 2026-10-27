<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import BillingBreakdown, {
    type BillingBreakdown as BillingBreakdownData,
} from '@/components/customer/BillingBreakdown.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import RatingSummary from '@/components/customer/RatingSummary.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { usePickupAddressAutofill } from '@/composables/usePickupAddressAutofill';
import {
    serviceCategoryLabel,
    type ServiceCategory,
} from '@/constants/serviceCategories';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { searchPlaces } from '@/lib/maps';
import type { AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    Info,
    Loader2,
    MapPin,
    Package,
    Phone,
    WashingMachine,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopService {
    id: number;
    category: ServiceCategory | string;
    category_label: string | null;
    supplies: {
        name: string;
        quantity: number;
        unit: string;
        per: 'kg' | 'bundle' | 'piece';
    }[];
    service_name: string;
    description: string | null;
    pricing_model: 'per_kg' | 'per_bundle' | 'per_piece';
    price_per_piece: string | null;
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    photo_url: string | null;
}

interface ShopInfo {
    id: number;
    shop_name: string;
    phone: string | null;
    block_street: string | null;
    municipality: string;
    barangay: string;
    cover_photo: string | null;
    latitude: number | null;
    longitude: number | null;
    distance_km: number | null;
    offers_pickup: boolean;
    offers_delivery: boolean;
    rating_avg: number | null;
    rating_count: number;
    payment_options: Record<
        'cash' | 'gcash' | 'maya',
        'at_shop' | 'online' | 'unavailable'
    >;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shop: ShopInfo;
    services: ShopService[];
    userLat: number | null;
    userLng: number | null;
    customerAgreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}>();

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const errors = computed(() => page.props.errors as Record<string, string>);

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flashToast?.type === 'success') toast.success(flashToast.message);
    else if (flashToast?.type === 'error') toast.error(flashToast.message);
});

// ─── Order form ───────────────────────────────────────────────────────────────

const isOrderOpen = ref(false);
const placing = ref(false);
const selectedService = ref<ShopService | null>(null);

const paymentOptions = [
    {
        value: 'cash',
        label: 'Pay at Shop',
        icon: Banknote,
        image: null,
    },
    {
        value: 'gcash',
        label: 'GCash',
        icon: null,
        image: 'https://upload.wikimedia.org/wikipedia/commons/5/52/GCash_logo.svg',
    },
    {
        value: 'maya',
        label: 'Maya',
        icon: null,
        image: 'https://upload.wikimedia.org/wikipedia/commons/e/e6/Maya_logo.svg',
    },
];

// How this shop takes each payment method, as decided by the server.
function paymentNote(method: string): { text: string; warn: boolean } {
    if (method === 'cash') {
        return { text: 'Pay cash when you drop off or pick up.', warn: false };
    }

    const key = method as 'gcash' | 'maya';
    const label = key === 'gcash' ? 'GCash' : 'Maya';
    const mode = props.shop.payment_options[key];

    if (mode === 'online') {
        return {
            text: 'Pay online after the shop confirms the actual weight and final amount.',
            warn: false,
        };
    }

    return {
        text: `This shop has not set up ${label} yet. Ask the shop first, or pay at the shop.`,
        warn: true,
    };
}

const form = ref({
    shop_id: props.shop.id,
    service_id: 0,
    customer_name: user.value.name,
    customer_phone: '',
    estimated_weight_kg: '',
    piece_count: '',
    pickup_type: 'walk_in' as 'walk_in' | 'pickup',
    payment_method: 'cash' as 'cash' | 'gcash' | 'maya',
    customer_address: '',
    customer_lat: null as number | null,
    customer_lng: null as number | null,
    special_instructions: '',
    customer_agreement_version: props.customerAgreement.version,
    customer_agreement_accepted: false,
});

// Pre-fill the pickup address from the device location; the customer can still edit it.
const { locating, failed, detectLocation } = usePickupAddressAutofill();

// The address the saved pin belongs to; once the customer edits the text the pin is out of date.
let pinnedAddress = '';
const PIN_LOOKUP_TIMEOUT_MS = 4_000;

async function autofillAddress(force = false) {
    if (!force && form.value.customer_address.trim() !== '') return;

    const detected = await detectLocation();
    if (
        detected &&
        form.value.pickup_type === 'pickup' &&
        (force || form.value.customer_address.trim() === '')
    ) {
        // Without a readable address the position cannot be matched to the text, so no pin is kept.
        if (detected.address) {
            form.value.customer_address = detected.address;
            form.value.customer_lat = detected.lat;
            form.value.customer_lng = detected.lng;
            pinnedAddress = detected.address;
        }
    }
}

/** Makes sure a pickup order carries a pin that matches the address, so the rider can be tracked. */
async function resolvePickupPin() {
    const address = form.value.customer_address.trim();

    if (form.value.pickup_type !== 'pickup') {
        form.value.customer_lat = form.value.customer_lng = null;
        return;
    }
    if (form.value.customer_lat !== null && address === pinnedAddress.trim()) {
        return;
    }

    form.value.customer_lat = form.value.customer_lng = null;
    if (address === '') return;

    try {
        // Never keep the customer waiting on the address lookup.
        const [match] = await Promise.race([
            searchPlaces(address),
            new Promise<never[]>((resolve) =>
                setTimeout(() => resolve([]), PIN_LOOKUP_TIMEOUT_MS),
            ),
        ]);
        if (match) {
            form.value.customer_lat = Number(match.lat);
            form.value.customer_lng = Number(match.lon);
            pinnedAddress = form.value.customer_address;
        }
    } catch {
        // Without a pin the order is still placed; tracking just shows no route to the customer.
    }
}

watch(
    () => form.value.pickup_type,
    (type) => {
        if (type === 'pickup') autofillAddress();
    },
);

function openOrder(service: ShopService) {
    selectedService.value = service;
    form.value.service_id = service.id;
    // Each service is counted differently, so start the amount fields fresh.
    form.value.estimated_weight_kg = '';
    form.value.piece_count = '';
    isOrderOpen.value = true;
    form.value.customer_agreement_accepted = false;
}

function closeOrder() {
    isOrderOpen.value = false;
    selectedService.value = null;
}

const selectedPayment = computed(() =>
    paymentOptions.find((o) => o.value === form.value.payment_method),
);

// The server prices the estimate so it always matches what the order stores.
const estimate = ref<BillingBreakdownData | null>(null);
let estimateTimer: ReturnType<typeof setTimeout> | undefined;
let estimateRequest: AbortController | null = null;

async function loadEstimate() {
    estimateRequest?.abort();
    const request = new AbortController();
    estimateRequest = request;

    const query = new URLSearchParams({
        shop_id: String(props.shop.id),
        service_id: String(form.value.service_id),
    });
    if (isPerPiece.value) {
        query.set('piece_count', form.value.piece_count);
    } else {
        query.set('estimated_weight_kg', form.value.estimated_weight_kg);
    }

    try {
        const response = await fetch(`/user/orders/estimate?${query}`, {
            signal: request.signal,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        estimate.value = response.ok ? await response.json() : null;
    } catch {
        if (!request.signal.aborted) estimate.value = null;
    }
}

const isPerPiece = computed(
    () => selectedService.value?.pricing_model === 'per_piece',
);

watch(
    () => [
        form.value.service_id,
        form.value.estimated_weight_kg,
        form.value.piece_count,
    ],
    () => {
        clearTimeout(estimateTimer);

        const amount = parseFloat(
            isPerPiece.value
                ? form.value.piece_count
                : form.value.estimated_weight_kg,
        );
        const valid = isPerPiece.value
            ? amount >= 1 && amount <= 500
            : amount >= 0.5 && amount <= 100;
        if (!form.value.service_id || !valid) {
            estimateRequest?.abort();
            estimate.value = null;
            return;
        }

        estimateTimer = setTimeout(loadEstimate, 300);
    },
);

onBeforeUnmount(() => {
    clearTimeout(estimateTimer);
    estimateRequest?.abort();
});

async function submitOrder() {
    if (placing.value) return;
    placing.value = true;
    await resolvePickupPin();
    router.post('/user/orders', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            closeOrder();
        },
        onFinish: () => {
            placing.value = false;
        },
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatAddress(shop: ShopInfo) {
    return [shop.block_street, shop.barangay, shop.municipality]
        .filter(Boolean)
        .join(', ');
}

function formatServicePrice(svc: ShopService) {
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg)
        return `₱${parseFloat(svc.price_per_kg).toFixed(0)}/kg`;
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price)
        return `₱${parseFloat(svc.bundle_price).toFixed(0)} / ${svc.bundle_weight_kg}kg bundle`;
    if (svc.pricing_model === 'per_piece' && svc.price_per_piece)
        return `₱${parseFloat(svc.price_per_piece).toFixed(0)}/piece`;
    return '—';
}

function goBack() {
    const params = props.userLat
        ? `?lat=${props.userLat}&lng=${props.userLng}`
        : '';
    router.visit(`/user/shops${params}`);
}
</script>

<template>
    <Head :title="shop.shop_name" />
    <UserLayout>
        <!-- ── Hero photo ─────────────────────────────────────── -->
        <div class="relative h-52 overflow-hidden">
            <img
                v-if="shop.cover_photo"
                :src="shop.cover_photo"
                :alt="shop.shop_name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-indigo-600"
            >
                <WashingMachine class="h-16 w-16 text-white/40" />
            </div>

            <!-- Gradient overlay -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/30"
            />

            <!-- Floating back button -->
            <button
                class="absolute top-4 left-4 flex h-9 w-9 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm transition hover:bg-black/60 active:scale-95"
                @click="goBack"
            >
                <ArrowLeft class="h-4 w-4" />
            </button>

            <!-- Distance pill -->
            <div
                v-if="shop.distance_km !== null"
                class="absolute top-4 right-4 flex items-center gap-1 rounded-full bg-black/40 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm"
            >
                <MapPin class="h-3 w-3" />
                {{ shop.distance_km }} km away
            </div>

            <!-- Shop name overlay at bottom of photo -->
            <div class="absolute right-0 bottom-0 left-0 px-4 pb-4">
                <h1
                    class="text-xl leading-tight font-extrabold text-white drop-shadow-md"
                >
                    {{ shop.shop_name }}
                </h1>
                <div
                    class="mt-1.5 inline-flex rounded-full bg-white/90 px-2.5 py-1"
                >
                    <RatingSummary
                        :average="shop.rating_avg"
                        :count="shop.rating_count"
                    />
                </div>
            </div>
        </div>

        <div class="space-y-5 px-4 pt-4 pb-8">
            <!-- ── Shop info card ─────────────────────────────── -->
            <div
                class="space-y-2.5 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" />
                    <span>{{ formatAddress(shop) }}</span>
                </div>
                <div
                    v-if="shop.phone"
                    class="flex items-center gap-2 text-sm text-gray-600"
                >
                    <Phone class="h-4 w-4 shrink-0 text-blue-500" />
                    <a
                        :href="`tel:${shop.phone}`"
                        class="transition hover:text-blue-600"
                        >{{ shop.phone }}</a
                    >
                </div>
            </div>

            <!-- ── Services ───────────────────────────────────── -->
            <div>
                <h2 class="mb-3 text-base font-bold text-gray-800">
                    Available Services
                </h2>

                <div
                    v-if="services.length === 0"
                    class="flex flex-col items-center py-12 text-gray-400"
                >
                    <Package class="mb-2 h-10 w-10 opacity-40" />
                    <p class="text-sm">No services available at this time.</p>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="svc in services"
                        :key="svc.id"
                        class="flex min-h-80 flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md"
                    >
                        <div>
                            <img
                                v-if="svc.photo_url"
                                :src="svc.photo_url"
                                :alt="svc.service_name"
                                loading="lazy"
                                class="mb-3 h-36 w-full rounded-xl object-cover"
                            />
                            <span
                                class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700"
                            >
                                {{
                                    svc.category_label ??
                                    serviceCategoryLabel(
                                        svc.category as ServiceCategory,
                                    )
                                }}
                            </span>
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ svc.service_name }}
                            </h3>
                            <p class="mt-1.5 text-sm leading-6 text-gray-500">
                                {{
                                    svc.description ||
                                    'Professional laundry service handled with care.'
                                }}
                            </p>

                            <p
                                class="mt-4 text-xl font-extrabold text-blue-600"
                            >
                                {{ formatServicePrice(svc) }}
                            </p>

                            <div
                                v-if="svc.supplies.length"
                                class="mt-3 rounded-xl bg-gray-50 p-3"
                            >
                                <p
                                    class="flex items-center gap-1.5 text-xs font-semibold text-gray-700"
                                >
                                    <Package class="h-3.5 w-3.5" />
                                    Supplies used in this service
                                </p>
                                <ul class="mt-1.5 space-y-0.5">
                                    <li
                                        v-for="supply in svc.supplies"
                                        :key="supply.name"
                                        class="text-xs text-gray-600"
                                    >
                                        {{ supply.name }}
                                        <span class="text-gray-400"
                                            >· {{ supply.quantity }}
                                            {{ supply.unit }} per
                                            {{ supply.per }}</span
                                        >
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs text-amber-700"
                                >
                                    <Info class="h-3.5 w-3.5" />
                                    Final price after weighing
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-auto w-full rounded-xl border-2 border-blue-600 px-4 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-600 hover:text-white active:bg-blue-700"
                            @click="openOrder(svc)"
                        >
                            Place an order
                        </button>
                    </article>
                </div>
            </div>
        </div>

        <!-- ── Order Dialog ────────────────────────────────────── -->
        <Dialog v-model:open="isOrderOpen">
            <DialogContent
                class="mx-4 max-h-[90vh] max-w-sm overflow-y-auto rounded-2xl"
            >
                <DialogHeader>
                    <DialogTitle class="text-base">
                        Place Order — {{ selectedService?.service_name }}
                    </DialogTitle>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <!-- Shop reminder -->
                    <div
                        class="flex items-start gap-2 rounded-xl bg-blue-50 p-3 text-xs text-blue-700"
                    >
                        <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <div>
                            <strong>{{ shop.shop_name }}</strong
                            ><br />
                            {{ formatAddress(shop) }}
                        </div>
                    </div>

                    <!-- Customer name -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Your Name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.customer_name"
                            placeholder="Full name"
                            :class="
                                errors.customer_name ? 'border-red-400' : ''
                            "
                        />
                        <p
                            v-if="errors.customer_name"
                            class="text-xs text-red-500"
                        >
                            {{ errors.customer_name }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Phone Number
                            <span class="text-red-500">*</span></label
                        >
                        <PhilippinePhoneInput
                            v-model="form.customer_phone"
                            :class="
                                errors.customer_phone ? 'border-red-400' : ''
                            "
                        />
                        <p
                            v-if="errors.customer_phone"
                            class="text-xs text-red-500"
                        >
                            {{ errors.customer_phone }}
                        </p>
                    </div>

                    <!-- Estimated weight, or the number of pieces -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >{{
                                isPerPiece
                                    ? 'Number of pieces'
                                    : 'Estimated Weight (kg)'
                            }}
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-if="isPerPiece"
                            v-model="form.piece_count"
                            type="number"
                            min="1"
                            max="500"
                            step="1"
                            placeholder="e.g. 2"
                            :class="errors.piece_count ? 'border-red-400' : ''"
                        />
                        <Input
                            v-else
                            v-model="form.estimated_weight_kg"
                            type="number"
                            min="0.5"
                            step="0.5"
                            placeholder="e.g. 3"
                            :class="
                                errors.estimated_weight_kg
                                    ? 'border-red-400'
                                    : ''
                            "
                        />
                        <p
                            v-if="
                                errors.piece_count || errors.estimated_weight_kg
                            "
                            class="text-xs text-red-500"
                        >
                            {{
                                errors.piece_count || errors.estimated_weight_kg
                            }}
                        </p>
                        <BillingBreakdown
                            v-if="estimate"
                            :breakdown="estimate"
                            :show-payment="false"
                        />
                    </div>

                    <!-- Pickup type -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >How will you drop-off?
                            <span class="text-red-500">*</span></label
                        >
                        <div
                            class="grid gap-2"
                            :class="
                                shop.offers_pickup
                                    ? 'grid-cols-2'
                                    : 'grid-cols-1'
                            "
                        >
                            <button
                                v-if="shop.offers_pickup"
                                type="button"
                                class="rounded-xl border-2 py-2.5 text-sm font-medium transition"
                                :class="
                                    form.pickup_type === 'walk_in'
                                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                "
                                @click="form.pickup_type = 'walk_in'"
                            >
                                Walk-in
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border-2 py-2.5 text-sm font-medium transition"
                                :class="
                                    form.pickup_type === 'pickup'
                                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                "
                                @click="form.pickup_type = 'pickup'"
                            >
                                Pickup
                            </button>
                        </div>
                    </div>

                    <!-- Address (pickup only) -->
                    <div
                        v-if="form.pickup_type === 'pickup'"
                        class="space-y-1.5"
                    >
                        <label class="text-sm font-medium"
                            >Pickup Address
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.customer_address"
                            placeholder="Your complete address"
                            :class="
                                errors.customer_address ? 'border-red-400' : ''
                            "
                        />
                        <div class="flex items-center gap-2 text-xs">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 font-medium text-blue-600 hover:underline disabled:opacity-60"
                                :disabled="locating"
                                @click="autofillAddress(true)"
                            >
                                <Loader2
                                    v-if="locating"
                                    class="h-3 w-3 animate-spin"
                                />
                                <MapPin v-else class="h-3 w-3" />
                                {{
                                    locating
                                        ? 'Finding your address…'
                                        : 'Use my current location'
                                }}
                            </button>
                            <span v-if="failed" class="text-muted-foreground"
                                >Could not detect it. Please type your
                                address.</span
                            >
                        </div>
                        <p
                            v-if="errors.customer_address"
                            class="text-xs text-red-500"
                        >
                            {{ errors.customer_address }}
                        </p>
                    </div>

                    <!-- Payment method -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Payment Method
                            <span class="text-red-500">*</span></label
                        >
                        <div class="space-y-2">
                            <button
                                v-for="opt in paymentOptions"
                                :key="opt.value"
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border-2 p-3 text-left transition"
                                :class="
                                    form.payment_method === opt.value
                                        ? 'border-blue-600 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                "
                                @click="
                                    form.payment_method = opt.value as
                                        | 'cash'
                                        | 'gcash'
                                        | 'maya'
                                "
                            >
                                <img
                                    v-if="opt.image"
                                    :src="opt.image"
                                    :alt="`${opt.label} logo`"
                                    class="h-7 w-12 shrink-0 object-contain"
                                />
                                <component
                                    :is="opt.icon"
                                    v-else
                                    class="h-5 w-5 shrink-0"
                                    :class="
                                        form.payment_method === opt.value
                                            ? 'text-blue-600'
                                            : 'text-gray-400'
                                    "
                                />
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-medium"
                                        :class="
                                            form.payment_method === opt.value
                                                ? 'text-blue-700'
                                                : 'text-gray-700'
                                        "
                                    >
                                        {{ opt.label }}
                                    </p>
                                    <p
                                        class="text-xs"
                                        :class="
                                            paymentNote(opt.value).warn
                                                ? 'text-amber-600'
                                                : 'text-gray-400'
                                        "
                                    >
                                        {{ paymentNote(opt.value).text }}
                                    </p>
                                </div>
                            </button>
                        </div>
                        <p
                            v-if="errors.payment_method"
                            class="text-xs text-red-500"
                        >
                            {{ errors.payment_method }}
                        </p>
                    </div>

                    <!-- Special instructions -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Special Instructions
                            <span class="text-xs text-gray-400"
                                >(optional)</span
                            ></label
                        >
                        <Input
                            v-model="form.special_instructions"
                            placeholder="e.g. Separate whites, use fabric conditioner..."
                        />
                    </div>

                    <CustomerServiceAgreement
                        v-model="form.customer_agreement_accepted"
                        :agreement="customerAgreement"
                    />
                    <p
                        v-if="errors.customer_agreement_accepted"
                        class="text-xs text-red-500"
                    >
                        {{ errors.customer_agreement_accepted }}
                    </p>

                    <!-- Disclaimer -->
                    <div
                        class="rounded-xl bg-amber-50 p-3 text-xs text-amber-700"
                    >
                        <template v-if="isPerPiece">
                            <strong>Note:</strong> This service is priced per
                            piece, so the amount above is the final amount. The
                            shop will count your items when you drop them off.
                            If you selected
                        </template>
                        <template v-else>
                            <strong>Note:</strong> The amount above is only an
                            estimate. The shop will weigh your laundry and
                            confirm the final amount. If you selected
                        </template>
                        <strong>{{ selectedPayment?.label }}</strong
                        >, online payment will become available afterward.
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" class="flex-1" @click="closeOrder"
                        >Cancel</Button
                    >
                    <Button
                        class="flex-1"
                        :disabled="placing"
                        @click="submitOrder"
                    >
                        <Loader2
                            v-if="placing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ placing ? 'Placing...' : 'Place Order' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </UserLayout>
</template>
