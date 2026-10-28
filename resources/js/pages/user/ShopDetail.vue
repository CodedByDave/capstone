<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import BillingBreakdown, {
    type BillingBreakdown as BillingBreakdownData,
} from '@/components/customer/BillingBreakdown.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
} from '@/components/ui/sheet';
import { usePickupAddressAutofill } from '@/composables/usePickupAddressAutofill';
import {
    serviceCategoryLabel,
    type ServiceCategory,
} from '@/constants/serviceCategories';
import UserLayout from '@/layouts/user/UserLayout.vue';
import { searchPlaces } from '@/lib/maps';
import type { AppPageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Bell,
    Loader2,
    MapPin,
    Package,
    Phone,
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

const form = ref({
    shop_id: props.shop.id,
    service_id: 0,
    customer_name: user.value.name,
    customer_phone: '',
    estimated_weight_kg: '',
    piece_count: '',
    pickup_type: 'walk_in' as 'walk_in' | 'pickup',
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

// Underline-style fields, as in the order sheet design.
const fieldClass =
    'h-auto rounded-none border-0 border-b border-border bg-transparent px-0 py-2 text-lg shadow-none focus-visible:border-primary focus-visible:ring-0';

// What the sheet needs before Place order turns on, and what to tell the customer while it is off.
const PH_MOBILE = /^\+63-9\d{2}-\d{4}-\d{3}$/;

const weightHelp = computed(() => {
    const svc = selectedService.value;
    if (!svc) return '';
    if (svc.pricing_model === 'per_piece') {
        return 'Priced per piece. The shop counts your items at drop-off.';
    }
    if (svc.pricing_model === 'per_bundle') {
        return `Priced per ${parseFloat(svc.bundle_weight_kg ?? '') || ''} kg bundle. The shop confirms the real weight.`;
    }

    return 'Priced per kg. The shop confirms the real weight.';
});

const orderHint = computed(() => {
    const f = form.value;
    if (f.customer_name.trim() === '') return 'Enter your full name.';
    if (!PH_MOBILE.test(f.customer_phone)) return 'Enter a valid phone number.';

    const amount = parseFloat(
        isPerPiece.value ? f.piece_count : f.estimated_weight_kg,
    );
    const validAmount = isPerPiece.value
        ? amount >= 1 && amount <= 500
        : amount >= 0.5 && amount <= 100;
    if (!validAmount) {
        return isPerPiece.value
            ? 'Enter the number of pieces.'
            : 'Enter your estimated weight.';
    }
    if (f.pickup_type === 'pickup' && f.customer_address.trim() === '') {
        return 'Enter your pickup address.';
    }
    if (!f.customer_agreement_accepted) {
        return 'Accept the service agreement to continue.';
    }

    return '';
});

const canPlaceOrder = computed(() => orderHint.value === '' && !placing.value);

const estimatedTotal = computed(() =>
    estimate.value
        ? `₱${estimate.value.total.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
        : '—',
);

function closeOrder() {
    isOrderOpen.value = false;
    selectedService.value = null;
}

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

function money(value: string | number) {
    return `₱${parseFloat(String(value)).toFixed(0)}`;
}

function servicePrice(svc: ShopService): {
    amount: string;
    unit: string;
    perKg: string | null;
} {
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg)
        return { amount: money(svc.price_per_kg), unit: '/ kg', perKg: null };
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price) {
        const weight = parseFloat(svc.bundle_weight_kg ?? '');
        return {
            amount: money(svc.bundle_price),
            unit: `/ ${svc.bundle_weight_kg} kg bundle`,
            perKg:
                weight > 0
                    ? `${money(parseFloat(svc.bundle_price) / weight)} per kg`
                    : null,
        };
    }
    if (svc.pricing_model === 'per_piece' && svc.price_per_piece)
        return {
            amount: money(svc.price_per_piece),
            unit: '/ piece',
            perKg: null,
        };
    return { amount: '—', unit: '', perKg: null };
}

function directionsUrl(shop: ShopInfo) {
    const destination =
        shop.latitude !== null && shop.longitude !== null
            ? `${shop.latitude},${shop.longitude}`
            : formatAddress(shop);
    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`;
}

function goBack() {
    router.visit('/user/dashboard');
}
</script>

<template>
    <Head :title="shop.shop_name" />
    <UserLayout hide-header>
        <!-- ── Hero ───────────────────────────────────────────── -->
        <div class="relative h-60 overflow-hidden">
            <img
                v-if="shop.cover_photo"
                :src="shop.cover_photo"
                :alt="shop.shop_name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="h-full w-full bg-gradient-to-b from-blue-500 to-blue-900"
            />
            <div
                class="absolute inset-0 bg-gradient-to-t from-blue-950/80 via-blue-900/20 to-transparent"
            />

            <button
                type="button"
                class="absolute top-4 left-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-800 transition hover:bg-white active:scale-95"
                aria-label="Back to home"
                @click="goBack"
            >
                <ArrowLeft class="h-5 w-5" />
            </button>
            <Link
                href="/user/notifications"
                class="absolute top-4 right-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-slate-800 transition hover:bg-white active:scale-95"
                aria-label="Notifications"
            >
                <Bell class="h-5 w-5" />
            </Link>

            <div class="absolute right-0 bottom-0 left-0 px-5 pb-5">
                <h1
                    class="font-display text-[28px] leading-9 font-bold text-white"
                >
                    {{ shop.shop_name }}
                </h1>
                <p class="mt-0.5 text-xs text-white/90">
                    <template v-if="shop.rating_count > 0">
                        ★ {{ shop.rating_avg?.toFixed(1) }} ·
                        {{ shop.rating_count }} rating{{
                            shop.rating_count === 1 ? '' : 's'
                        }}
                    </template>
                    <template v-else>No ratings yet</template>
                </p>
            </div>
        </div>

        <!-- ── Contact rows ───────────────────────────────────── -->
        <div class="px-5">
            <div
                class="flex min-h-14 items-center gap-3 border-b border-slate-100 py-3"
            >
                <MapPin class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                <span class="min-w-0 flex-1 text-sm leading-5 text-slate-800">{{
                    formatAddress(shop)
                }}</span>
                <a
                    :href="directionsUrl(shop)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="shrink-0 text-xs font-medium text-blue-600 hover:underline"
                    >Directions</a
                >
            </div>
            <div
                v-if="shop.phone"
                class="flex min-h-14 items-center gap-3 border-b border-slate-100 py-3"
            >
                <Phone class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                <span class="min-w-0 flex-1 text-sm text-slate-800">{{
                    shop.phone
                }}</span>
                <a
                    :href="`tel:${shop.phone}`"
                    class="shrink-0 text-xs font-medium text-blue-600 hover:underline"
                    >Call</a
                >
            </div>

            <!-- ── Services ───────────────────────────────────── -->
            <section class="mt-6">
                <div class="flex items-center justify-between">
                    <h2
                        class="text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                    >
                        Services
                    </h2>
                    <span class="text-xs text-slate-500"
                        >{{ services.length }} available</span
                    >
                </div>

                <div
                    v-if="services.length === 0"
                    class="flex flex-col items-center py-12 text-center"
                >
                    <Package
                        class="mb-2 h-8 w-8 text-slate-300"
                        aria-hidden="true"
                    />
                    <p class="text-sm text-slate-500">
                        No services available at this time.
                    </p>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="svc in services" :key="svc.id" class="py-6">
                        <img
                            v-if="svc.photo_url"
                            :src="svc.photo_url"
                            :alt="svc.service_name"
                            loading="lazy"
                            class="mb-3 h-36 w-full rounded-xl object-cover"
                        />
                        <p class="text-xs font-medium text-blue-600">
                            {{
                                svc.category_label ??
                                serviceCategoryLabel(
                                    svc.category as ServiceCategory,
                                )
                            }}
                        </p>
                        <h3
                            class="mt-1 font-display text-[22px] leading-7 font-bold text-slate-900"
                        >
                            {{ svc.service_name }}
                        </h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-500">
                            {{
                                svc.description ||
                                'Professional laundry service handled with care.'
                            }}
                        </p>

                        <p class="mt-4 flex flex-wrap items-baseline gap-x-2">
                            <span
                                class="font-display text-[32px] leading-10 font-bold text-slate-900"
                                >{{ servicePrice(svc).amount }}</span
                            >
                            <span class="text-sm text-slate-500">{{
                                servicePrice(svc).unit
                            }}</span>
                            <span
                                v-if="servicePrice(svc).perKg"
                                class="ml-auto text-sm text-slate-500"
                                >≈ {{ servicePrice(svc).perKg }}</span
                            >
                        </p>

                        <p
                            v-if="svc.supplies.length"
                            class="mt-2 text-xs leading-5 text-slate-500"
                        >
                            Includes
                            {{
                                svc.supplies
                                    .map(
                                        (supply) =>
                                            `${supply.name} · ${supply.quantity} ${supply.unit} per ${supply.per}`,
                                    )
                                    .join(', ')
                            }}
                        </p>
                        <p
                            class="mt-2 flex items-center gap-2 text-xs text-slate-500"
                        >
                            <span
                                class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"
                                aria-hidden="true"
                            />
                            Final price is set after your laundry is weighed.
                        </p>

                        <button
                            type="button"
                            class="mt-5 flex min-h-12 w-full items-center justify-center rounded-xl border border-blue-600 bg-white text-sm font-semibold text-blue-600 transition hover:bg-blue-50 active:bg-blue-100"
                            @click="openOrder(svc)"
                        >
                            Place an order
                        </button>
                    </li>
                </ul>
            </section>
        </div>

        <!-- ── Order sheet ─────────────────────────────────────── -->
        <Sheet v-model:open="isOrderOpen">
            <SheetContent
                side="bottom"
                class="mx-auto max-h-[92dvh] max-w-lg gap-0 overflow-hidden rounded-t-[28px] border-t-0 p-0 [&>button:last-of-type]:top-6 [&>button:last-of-type]:right-5 [&>button:last-of-type]:flex [&>button:last-of-type]:size-10 [&>button:last-of-type]:items-center [&>button:last-of-type]:justify-center [&>button:last-of-type]:rounded-full [&>button:last-of-type]:bg-muted [&>button:last-of-type]:opacity-100 [&>button:last-of-type_svg]:size-5"
            >
                <!-- Grabber + title -->
                <div class="px-6 pt-3 pb-4">
                    <div
                        class="mx-auto mb-5 h-1 w-10 rounded-full bg-muted-foreground/30"
                        aria-hidden="true"
                    />
                    <SheetTitle
                        class="pr-12 text-2xl font-bold tracking-tight text-foreground"
                    >
                        Place an order
                    </SheetTitle>
                    <SheetDescription class="mt-1 pr-12 text-sm">
                        {{ selectedService?.service_name }} ·
                        {{ shop.shop_name }}, {{ shop.barangay }}
                    </SheetDescription>
                </div>

                <!-- Scrolling form -->
                <div class="min-h-0 flex-1 space-y-7 overflow-y-auto px-6 pb-6">
                    <!-- Your details -->
                    <section class="space-y-5">
                        <p
                            class="text-[11px] font-semibold tracking-[0.16em] text-muted-foreground uppercase"
                        >
                            Your details
                        </p>

                        <div>
                            <label
                                for="order-name"
                                class="text-sm font-medium text-muted-foreground"
                                >Full name</label
                            >
                            <Input
                                id="order-name"
                                v-model="form.customer_name"
                                autocomplete="name"
                                :class="[
                                    fieldClass,
                                    errors.customer_name && 'border-red-400',
                                ]"
                            />
                            <p
                                v-if="errors.customer_name"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ errors.customer_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="order-phone"
                                class="text-sm font-medium text-muted-foreground"
                                >Phone number</label
                            >
                            <PhilippinePhoneInput
                                id="order-phone"
                                v-model="form.customer_phone"
                                :class="[
                                    fieldClass,
                                    errors.customer_phone && 'border-red-400',
                                ]"
                            />
                            <p
                                v-if="errors.customer_phone"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ errors.customer_phone }}
                            </p>
                        </div>
                    </section>

                    <!-- Laundry -->
                    <section class="space-y-5">
                        <p
                            class="text-[11px] font-semibold tracking-[0.16em] text-muted-foreground uppercase"
                        >
                            Laundry
                        </p>

                        <div>
                            <label
                                for="order-amount"
                                class="text-sm font-semibold text-muted-foreground"
                                >{{
                                    isPerPiece
                                        ? 'Number of pieces'
                                        : 'Estimated weight (kg)'
                                }}</label
                            >
                            <Input
                                v-if="isPerPiece"
                                id="order-amount"
                                v-model="form.piece_count"
                                type="number"
                                inputmode="numeric"
                                min="1"
                                max="500"
                                step="1"
                                placeholder="e.g. 2"
                                :class="[
                                    fieldClass,
                                    errors.piece_count && 'border-red-400',
                                ]"
                            />
                            <Input
                                v-else
                                id="order-amount"
                                v-model="form.estimated_weight_kg"
                                type="number"
                                inputmode="decimal"
                                min="0.5"
                                step="0.5"
                                placeholder="e.g. 3"
                                :class="[
                                    fieldClass,
                                    errors.estimated_weight_kg &&
                                        'border-red-400',
                                ]"
                            />
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ weightHelp }}
                            </p>
                            <p
                                v-if="
                                    errors.piece_count ||
                                    errors.estimated_weight_kg
                                "
                                class="mt-1 text-xs text-red-500"
                            >
                                {{
                                    errors.piece_count ||
                                    errors.estimated_weight_kg
                                }}
                            </p>
                            <BillingBreakdown
                                v-if="estimate"
                                class="mt-3"
                                :breakdown="estimate"
                                :show-payment="false"
                            />
                        </div>

                        <!-- Drop-off -->
                        <div>
                            <p
                                class="text-sm font-semibold text-muted-foreground"
                            >
                                How will you drop off?
                            </p>
                            <div
                                class="mt-2 grid gap-1 rounded-2xl bg-primary/10 p-1"
                                :class="
                                    shop.offers_pickup
                                        ? 'grid-cols-2'
                                        : 'grid-cols-1'
                                "
                                role="radiogroup"
                                aria-label="How will you drop off?"
                            >
                                <button
                                    v-if="shop.offers_pickup"
                                    type="button"
                                    role="radio"
                                    :aria-checked="
                                        form.pickup_type === 'walk_in'
                                    "
                                    class="h-12 rounded-xl text-base font-semibold transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="
                                        form.pickup_type === 'walk_in'
                                            ? 'bg-background text-primary shadow-sm'
                                            : 'text-muted-foreground'
                                    "
                                    @click="form.pickup_type = 'walk_in'"
                                >
                                    Walk-in
                                </button>
                                <button
                                    type="button"
                                    role="radio"
                                    :aria-checked="
                                        form.pickup_type === 'pickup'
                                    "
                                    class="h-12 rounded-xl text-base font-semibold transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    :class="
                                        form.pickup_type === 'pickup'
                                            ? 'bg-background text-primary shadow-sm'
                                            : 'text-muted-foreground'
                                    "
                                    @click="form.pickup_type = 'pickup'"
                                >
                                    Pickup
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{
                                    form.pickup_type === 'pickup'
                                        ? 'We collect your laundry from your address.'
                                        : 'Bring your laundry to the shop.'
                                }}
                            </p>
                        </div>

                        <!-- Address (pickup only) -->
                        <div v-if="form.pickup_type === 'pickup'">
                            <label
                                for="order-address"
                                class="text-sm font-semibold text-muted-foreground"
                                >Pickup address</label
                            >
                            <Input
                                id="order-address"
                                v-model="form.customer_address"
                                autocomplete="street-address"
                                placeholder="Your complete address"
                                :class="[
                                    fieldClass,
                                    errors.customer_address && 'border-red-400',
                                ]"
                            />
                            <div class="mt-2 flex items-center gap-2 text-sm">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 font-semibold text-primary disabled:opacity-60"
                                    :disabled="locating"
                                    @click="autofillAddress(true)"
                                >
                                    <Loader2
                                        v-if="locating"
                                        class="h-4 w-4 animate-spin"
                                    />
                                    <MapPin v-else class="h-4 w-4" />
                                    {{
                                        locating
                                            ? 'Finding your address…'
                                            : 'Use my current location'
                                    }}
                                </button>
                                <span
                                    v-if="failed"
                                    class="text-muted-foreground"
                                    >Could not detect it. Please type your
                                    address.</span
                                >
                            </div>
                            <p
                                v-if="errors.customer_address"
                                class="mt-1 text-xs text-red-500"
                            >
                                {{ errors.customer_address }}
                            </p>
                        </div>

                        <!-- Special instructions -->
                        <div>
                            <label
                                for="order-notes"
                                class="text-sm font-semibold text-muted-foreground"
                                >Special instructions
                                <span class="font-normal"
                                    >(optional)</span
                                ></label
                            >
                            <Input
                                id="order-notes"
                                v-model="form.special_instructions"
                                placeholder="e.g. Separate whites, use fabric conditioner"
                                :class="fieldClass"
                            />
                        </div>
                    </section>

                    <!-- Agreement and note -->
                    <section class="space-y-4">
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

                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                        >
                            <template v-if="isPerPiece">
                                This service is priced per piece, so the amount
                                above is the final amount. The shop counts your
                                items when you drop them off.
                            </template>
                            <template v-else>
                                The amount above is only an estimate. The shop
                                weighs your laundry and confirms the final
                                amount.
                            </template>
                            You can pay at the shop or online once the final
                            amount is confirmed.
                        </p>
                    </section>
                </div>

                <!-- Sticky footer: total and the one action -->
                <div
                    class="border-t border-border bg-background px-6 pt-4 pb-[max(1rem,env(safe-area-inset-bottom))]"
                >
                    <div class="flex items-baseline justify-between gap-4">
                        <span class="text-base text-muted-foreground">{{
                            estimate
                                ? 'Estimated total'
                                : isPerPiece
                                  ? 'Estimated total (add pieces)'
                                  : 'Estimated total (add weight)'
                        }}</span>
                        <span
                            class="text-xl font-bold text-foreground tabular-nums"
                            >{{ estimatedTotal }}</span
                        >
                    </div>
                    <button
                        type="button"
                        class="mt-3 flex h-14 w-full items-center justify-center gap-2 rounded-2xl text-lg font-semibold transition focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        :class="
                            canPlaceOrder
                                ? 'bg-primary text-primary-foreground shadow-md active:scale-[0.99]'
                                : 'cursor-not-allowed bg-muted text-muted-foreground'
                        "
                        :disabled="!canPlaceOrder"
                        @click="submitOrder"
                    >
                        <Loader2 v-if="placing" class="h-5 w-5 animate-spin" />
                        {{ placing ? 'Placing…' : 'Place order' }}
                    </button>
                    <p
                        class="mt-2 min-h-5 text-center text-sm text-muted-foreground"
                        role="status"
                    >
                        {{ orderHint }}
                    </p>
                </div>
            </SheetContent>
        </Sheet>
    </UserLayout>
</template>
