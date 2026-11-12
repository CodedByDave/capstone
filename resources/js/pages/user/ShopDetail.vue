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
    Clock,
    Loader2,
    MapPin,
    Minus,
    Package,
    Phone,
    Plus,
    ShoppingBag,
    Weight,
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
    photo_urls?: string[];
}

interface AdditionalChargeOption {
    id: number;
    name: string;
    quantity: number;
    unit: string;
    price: number;
}

interface RetailProduct {
    id: number;
    name: string;
    pack_size: number;
    unit: string;
    price: number;
    photo_url: string | null;
    in_stock: boolean;
    /** How many packs one order can take: the per-order limit, or fewer when stock is low. */
    max_count: number;
}

interface CustomerPromotion {
    id: number;
    name: string;
    headline: string;
    description: string | null;
    min_order_amount: number | null;
    ends_at: string | null;
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
    /** The weight range (kg) a rider collects; null means no limit on that side. */
    rider_load_limit?: { min: number | null; max: number | null };
    is_open: boolean;
    is_open_24_7: boolean;
    opens_at: string | null;
    closes_at: string | null;
    operating_days: number[];
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
    additionalCharges: AdditionalChargeOption[];
    retailProducts: RetailProduct[];
    promotions: CustomerPromotion[];
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
    alternate_contact_name: '',
    alternate_contact_phone: '',
    customer_lat: null as number | null,
    customer_lng: null as number | null,
    special_instructions: '',
    promotion_id: '' as string | number,
    customer_agreement_version: props.customerAgreement.version,
    customer_agreement_accepted: false,
});

// Additional charges the customer picks: how many of each shop add-on item to include.
const MAX_EXTRA = 10;
const extras = ref<Record<number, number>>({});
const additionalItems = computed(() =>
    Object.entries(extras.value)
        .filter(([, count]) => count > 0)
        .map(([id, count]) => ({ id: Number(id), count })),
);

function changeExtra(id: number, change: number) {
    const next = Math.min(
        MAX_EXTRA,
        Math.max(0, (extras.value[id] ?? 0) + change),
    );
    extras.value = { ...extras.value, [id]: next };
}

// Retail products in the cart: bought with a laundry order, or on their own and collected at the shop.
const cart = ref<Record<number, number>>({});
const retailItems = computed(() =>
    Object.entries(cart.value)
        .filter(([, count]) => count > 0)
        .map(([id, count]) => ({ id: Number(id), count })),
);
const inStockProducts = computed(() =>
    props.retailProducts.filter((product) => product.in_stock),
);
const cartCount = computed(() =>
    retailItems.value.reduce((sum, item) => sum + item.count, 0),
);
const cartSubtotal = computed(() =>
    retailItems.value.reduce((sum, item) => {
        const product = props.retailProducts.find((p) => p.id === item.id);
        return sum + (product ? product.price * item.count : 0);
    }, 0),
);

function changeCart(product: RetailProduct, change: number) {
    const next = Math.min(
        product.max_count,
        Math.max(0, (cart.value[product.id] ?? 0) + change),
    );
    cart.value = { ...cart.value, [product.id]: next };
}

// ─── Buying products on their own ─────────────────────────────────────────────

const isProductCheckoutOpen = ref(false);
const placingProducts = ref(false);
const productForm = ref({
    customer_name: user.value.name,
    customer_phone: '',
    special_instructions: '',
});
const productEstimate = ref<BillingBreakdownData | null>(null);
let productEstimateTimer: ReturnType<typeof setTimeout> | undefined;
let productEstimateRequest: AbortController | null = null;

async function loadProductEstimate() {
    productEstimateRequest?.abort();
    const request = new AbortController();
    productEstimateRequest = request;

    const query = new URLSearchParams({ shop_id: String(props.shop.id) });
    retailItems.value.forEach((item, index) => {
        query.set(`retail_items[${index}][id]`, String(item.id));
        query.set(`retail_items[${index}][count]`, String(item.count));
    });

    try {
        const response = await fetch(
            `/user/orders/products/estimate?${query}`,
            {
                signal: request.signal,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );
        productEstimate.value = response.ok ? await response.json() : null;
    } catch {
        if (!request.signal.aborted) productEstimate.value = null;
    }
}

watch(
    () => [isProductCheckoutOpen.value, JSON.stringify(retailItems.value)],
    () => {
        clearTimeout(productEstimateTimer);
        if (!isProductCheckoutOpen.value || retailItems.value.length === 0) {
            productEstimateRequest?.abort();
            productEstimate.value = null;
            return;
        }
        productEstimateTimer = setTimeout(loadProductEstimate, 200);
    },
);

const productCheckoutHint = computed(() => {
    if (!props.shop.is_open) return 'The shop is closed right now.';
    if (retailItems.value.length === 0) return 'Add at least one product.';
    if (productForm.value.customer_name.trim() === '')
        return 'Enter your name.';
    if (!PH_MOBILE.test(productForm.value.customer_phone))
        return 'Enter your mobile number.';
    return '';
});

function openProductCheckout() {
    if (!props.shop.is_open || retailItems.value.length === 0) return;
    isProductCheckoutOpen.value = true;
}

function placeProductOrder() {
    if (placingProducts.value || productCheckoutHint.value !== '') return;
    placingProducts.value = true;
    router.post(
        '/user/orders/products',
        {
            shop_id: props.shop.id,
            retail_items: retailItems.value,
            ...productForm.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                cart.value = {};
                isProductCheckoutOpen.value = false;
            },
            onFinish: () => {
                placingProducts.value = false;
            },
        },
    );
}

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
            form.value.customer_lng = Number(match.lng);
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

const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
// Monday first reads more naturally for a week.
const WEEK_ORDER = [1, 2, 3, 4, 5, 6, 0];

function formatTime(value: string | null) {
    if (!value) return '';
    const [h, m] = value.split(':').map(Number);
    return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
}

const hoursLabel = computed(() =>
    props.shop.is_open_24_7 || !props.shop.opens_at || !props.shop.closes_at
        ? 'Open 24 hours'
        : `${formatTime(props.shop.opens_at)} – ${formatTime(props.shop.closes_at)}`,
);

function openOrder(service: ShopService) {
    if (!props.shop.is_open) return;
    selectedService.value = service;
    form.value.service_id = service.id;
    // Each service is counted differently, so start the amount fields fresh.
    form.value.estimated_weight_kg = '';
    form.value.piece_count = '';
    form.value.promotion_id = '';
    extras.value = {};
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

// ─── Rider load limit ──────────────────────────────
const kgText = (value: number) => `${parseFloat(value.toFixed(2))} kg`;
const riderMin = computed(() => props.shop.rider_load_limit?.min ?? null);
const riderMax = computed(() => props.shop.rider_load_limit?.max ?? null);

// "Rider pickup: 3–15 kg" (both ends allowed), or null when the shop has no limit.
const riderLimitText = computed(() => {
    const min = riderMin.value;
    const max = riderMax.value;
    if (min !== null && max !== null) {
        return `Rider pickup is for ${kgText(min)} to ${kgText(max)} of laundry.`;
    }
    if (max !== null)
        return `Rider pickup is for up to ${kgText(max)} of laundry.`;
    if (min !== null)
        return `Rider pickup needs at least ${kgText(min)} of laundry.`;
    return null;
});

// Why the estimated weight cannot be collected by a rider, or null when it can. The server checks the same.
const pickupWeightProblem = computed(() => {
    if (isPerPiece.value) return null;
    const weight = parseFloat(form.value.estimated_weight_kg);
    if (Number.isNaN(weight)) return null;

    if (riderMax.value !== null && weight > riderMax.value) {
        return `Over ${kgText(riderMax.value)}: a rider cannot carry this much. Please drop it off at the shop instead.`;
    }
    if (riderMin.value !== null && weight < riderMin.value) {
        return `Rider pickup needs at least ${kgText(riderMin.value)} of laundry. Please drop it off at the shop instead.`;
    }
    return null;
});

const orderHint = computed(() => {
    const f = form.value;
    if (!props.shop.is_open) return 'This shop is closed right now.';
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
    if (f.pickup_type === 'pickup' && pickupWeightProblem.value) {
        return pickupWeightProblem.value;
    }
    if (f.pickup_type === 'pickup' && f.customer_address.trim() === '') {
        return 'Enter your pickup address.';
    }
    if (f.pickup_type === 'pickup') {
        if (f.alternate_contact_name.trim() === '') {
            return 'Enter an alternate contact name.';
        }
        if (!PH_MOBILE.test(f.alternate_contact_phone)) {
            return 'Enter a valid alternate contact number.';
        }
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
    if (form.value.promotion_id !== '') {
        query.set('promotion_id', String(form.value.promotion_id));
    }
    // A rider pickup adds the pickup fee; a per-km fee needs the pinned address.
    if (form.value.pickup_type === 'pickup') {
        query.set('pickup_type', 'pickup');
        if (
            form.value.customer_lat !== null &&
            form.value.customer_lng !== null
        ) {
            query.set('customer_lat', String(form.value.customer_lat));
            query.set('customer_lng', String(form.value.customer_lng));
        }
    }
    additionalItems.value.forEach((item, index) => {
        query.set(`additional_items[${index}][id]`, String(item.id));
        query.set(`additional_items[${index}][count]`, String(item.count));
    });
    retailItems.value.forEach((item, index) => {
        query.set(`retail_items[${index}][id]`, String(item.id));
        query.set(`retail_items[${index}][count]`, String(item.count));
    });

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
        form.value.promotion_id,
        JSON.stringify(additionalItems.value),
        JSON.stringify(retailItems.value),
        form.value.pickup_type,
        form.value.customer_lat,
        form.value.customer_lng,
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
    clearTimeout(productEstimateTimer);
    productEstimateRequest?.abort();
});

async function submitOrder() {
    if (placing.value) return;
    placing.value = true;
    await resolvePickupPin();
    router.post(
        '/user/orders',
        {
            ...form.value,
            additional_items: additionalItems.value,
            retail_items: retailItems.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                cart.value = {};
                closeOrder();
            },
            onFinish: () => {
                placing.value = false;
            },
        },
    );
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
            <div class="border-b border-slate-100 py-3">
                <div class="flex min-h-8 items-center gap-3">
                    <Clock class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                    <span class="min-w-0 flex-1 text-sm text-slate-800">{{
                        hoursLabel
                    }}</span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="
                            shop.is_open
                                ? 'bg-emerald-50 text-emerald-800'
                                : 'bg-red-50 text-red-800'
                        "
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="
                                shop.is_open ? 'bg-emerald-600' : 'bg-red-600'
                            "
                            aria-hidden="true"
                        />
                        {{ shop.is_open ? 'Operating' : 'Closed' }}
                    </span>
                </div>
                <ul
                    class="mt-2 ml-[30px] flex flex-wrap gap-1.5"
                    aria-label="Days open"
                >
                    <li
                        v-for="day in WEEK_ORDER"
                        :key="day"
                        class="rounded-md px-2 py-1 text-xs font-medium"
                        :class="
                            shop.operating_days.includes(day)
                                ? 'bg-blue-50 text-blue-800'
                                : 'bg-slate-100 text-slate-500 line-through'
                        "
                    >
                        {{ DAY_NAMES[day] }}
                        <span class="sr-only">{{
                            shop.operating_days.includes(day)
                                ? 'open'
                                : 'closed'
                        }}</span>
                    </li>
                </ul>
                <p
                    v-if="!shop.is_open"
                    class="mt-2 ml-[30px] text-xs text-red-700"
                >
                    This shop is closed right now, so orders cannot be placed.
                    Please come back on an open day during opening hours.
                </p>
            </div>
            <div
                class="flex min-h-14 items-center gap-3 border-b border-slate-100 py-3"
            >
                <MapPin class="h-[18px] w-[18px] shrink-0 text-slate-500" />
                <span class="min-w-0 flex-1 text-sm leading-5 text-slate-800">{{
                    formatAddress(shop)
                }}</span>
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
                        <div
                            v-if="(svc.photo_urls?.length ?? 0) > 1"
                            class="mb-3 flex snap-x snap-mandatory gap-2 overflow-x-auto"
                            :aria-label="`${svc.service_name} photos`"
                        >
                            <img
                                v-for="(url, i) in svc.photo_urls"
                                :key="url"
                                :src="url"
                                :alt="`${svc.service_name} photo ${i + 1}`"
                                loading="lazy"
                                class="h-36 w-[85%] shrink-0 snap-start rounded-xl object-cover"
                            />
                        </div>
                        <img
                            v-else-if="svc.photo_url"
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
                            class="mt-5 flex min-h-12 w-full items-center justify-center rounded-xl border text-sm font-semibold transition"
                            :class="
                                shop.is_open
                                    ? 'border-blue-600 bg-white text-blue-600 hover:bg-blue-50 active:bg-blue-100'
                                    : 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-500'
                            "
                            :disabled="!shop.is_open"
                            @click="openOrder(svc)"
                        >
                            {{
                                shop.is_open
                                    ? 'Place an order'
                                    : 'Shop is closed'
                            }}
                        </button>
                    </li>
                </ul>
            </section>

            <!-- ── Additional charges ─────────────────────────── -->
            <section v-if="additionalCharges.length" class="mt-8">
                <h2
                    class="text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    Additional charges
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Optional extras you can add when you place an order.
                </p>
                <ul
                    class="mt-3 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white"
                >
                    <li
                        v-for="item in additionalCharges"
                        :key="item.id"
                        class="flex items-center justify-between gap-3 px-4 py-3"
                    >
                        <span
                            class="flex items-center gap-2 text-sm text-slate-900"
                        >
                            <Plus
                                class="h-4 w-4 shrink-0 text-blue-600"
                                aria-hidden="true"
                            />
                            {{ item.name }}
                            <span class="text-xs text-slate-500"
                                >{{ item.quantity }} {{ item.unit }}</span
                            >
                        </span>
                        <span
                            class="text-sm font-semibold text-slate-900 tabular-nums"
                            >{{ money(item.price) }}</span
                        >
                    </li>
                </ul>
            </section>

            <!-- ── Products for sale ──────────────────────────── -->
            <section
                v-if="retailProducts.length"
                class="mt-8"
                :class="cartCount > 0 && 'pb-20'"
                aria-labelledby="products-heading"
            >
                <h2
                    id="products-heading"
                    class="text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    Products for sale
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Add them to a laundry order, or buy them on their own and
                    collect them at the shop.
                </p>
                <ul class="mt-3 grid grid-cols-2 gap-3">
                    <li
                        v-for="product in retailProducts"
                        :key="product.id"
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white"
                    >
                        <div
                            class="flex aspect-square items-center justify-center bg-slate-50"
                        >
                            <img
                                v-if="product.photo_url"
                                :src="product.photo_url"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                            <Package
                                v-else
                                class="h-10 w-10 text-slate-300"
                                aria-hidden="true"
                            />
                        </div>
                        <div class="flex flex-1 flex-col gap-1 p-3">
                            <p
                                class="text-sm leading-snug font-semibold text-slate-900"
                            >
                                {{ product.name }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ product.pack_size }} {{ product.unit }}
                            </p>
                            <p
                                class="text-sm font-semibold text-slate-900 tabular-nums"
                            >
                                {{ money(product.price) }}
                            </p>
                            <div class="mt-auto pt-2">
                                <span
                                    v-if="!product.in_stock"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl bg-slate-100 text-xs font-semibold text-slate-600"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-slate-400"
                                        aria-hidden="true"
                                    />
                                    Out of stock
                                </span>
                                <button
                                    v-else-if="!cart[product.id]"
                                    type="button"
                                    class="flex min-h-11 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-600 text-sm font-semibold text-blue-600 transition hover:bg-blue-50 active:bg-blue-100"
                                    :aria-label="`Add ${product.name} to cart`"
                                    @click="changeCart(product, 1)"
                                >
                                    <Plus class="h-4 w-4" aria-hidden="true" />
                                    Add
                                </button>
                                <div
                                    v-else
                                    class="flex items-center justify-between"
                                >
                                    <button
                                        type="button"
                                        class="flex size-11 items-center justify-center rounded-full border border-slate-200"
                                        :aria-label="`Remove one ${product.name}`"
                                        @click="changeCart(product, -1)"
                                    >
                                        <Minus
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                    <span
                                        class="text-sm font-semibold tabular-nums"
                                        aria-live="polite"
                                        >{{ cart[product.id] }}</span
                                    >
                                    <button
                                        type="button"
                                        class="flex size-11 items-center justify-center rounded-full border border-blue-600 text-blue-600 disabled:opacity-40"
                                        :aria-label="`Add one ${product.name}`"
                                        :disabled="
                                            cart[product.id] >=
                                            product.max_count
                                        "
                                        @click="changeCart(product, 1)"
                                    >
                                        <Plus
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                                <p
                                    v-if="
                                        product.in_stock &&
                                        product.max_count < 10
                                    "
                                    class="mt-1 text-center text-[11px] text-amber-700"
                                >
                                    Only {{ product.max_count }} left
                                </p>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <!-- ── Cart bar: buy the products on their own ─────────── -->
        <div
            v-if="cartCount > 0 && !isOrderOpen && !isProductCheckoutOpen"
            class="fixed inset-x-0 bottom-16 z-30 px-4 pb-2"
        >
            <div
                class="mx-auto flex max-w-lg items-center justify-between gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-lg"
            >
                <span class="flex items-center gap-2 text-sm">
                    <ShoppingBag class="h-4 w-4" aria-hidden="true" />
                    {{ cartCount }}
                    {{ cartCount === 1 ? 'product' : 'products' }} ·
                    <span class="font-semibold tabular-nums">{{
                        money(cartSubtotal)
                    }}</span>
                </span>
                <button
                    type="button"
                    class="min-h-11 rounded-xl bg-blue-600 px-4 text-sm font-semibold transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:bg-slate-600"
                    :disabled="!shop.is_open"
                    @click="openProductCheckout"
                >
                    {{ shop.is_open ? 'Buy products only' : 'Shop is closed' }}
                </button>
            </div>
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
                            <div v-if="promotions.length" class="mt-4">
                                <label
                                    for="order-promotion"
                                    class="text-sm font-medium text-muted-foreground"
                                    >Promotion</label
                                >
                                <select
                                    id="order-promotion"
                                    v-model="form.promotion_id"
                                    class="mt-1 block min-h-12 w-full rounded-xl border border-border bg-background px-3 text-base focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    <option value="">No promotion</option>
                                    <option
                                        v-for="promo in promotions"
                                        :key="promo.id"
                                        :value="promo.id"
                                    >
                                        {{ promo.name }} · {{ promo.headline
                                        }}{{
                                            promo.min_order_amount
                                                ? ` (min. ${money(promo.min_order_amount)})`
                                                : ''
                                        }}
                                    </option>
                                </select>
                                <p
                                    v-if="errors.promotion_id"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.promotion_id }}
                                </p>
                            </div>
                            <div v-if="additionalCharges.length" class="mt-4">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Additional charges (optional)
                                </p>
                                <ul
                                    class="mt-1 divide-y divide-border rounded-xl border border-border"
                                >
                                    <li
                                        v-for="item in additionalCharges"
                                        :key="item.id"
                                        class="flex items-center justify-between gap-3 px-3 py-2"
                                    >
                                        <span class="min-w-0 text-sm">
                                            <span class="block font-medium">{{
                                                item.name
                                            }}</span>
                                            <span
                                                class="block text-xs text-muted-foreground"
                                                >{{ item.quantity }}
                                                {{ item.unit }} ·
                                                {{ money(item.price) }}</span
                                            >
                                        </span>
                                        <span
                                            class="flex shrink-0 items-center gap-1"
                                        >
                                            <button
                                                type="button"
                                                class="flex size-11 items-center justify-center rounded-full border border-border disabled:opacity-40"
                                                :aria-label="`Remove one ${item.name}`"
                                                :disabled="!extras[item.id]"
                                                @click="
                                                    changeExtra(item.id, -1)
                                                "
                                            >
                                                <Minus
                                                    class="h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                            <span
                                                class="w-6 text-center text-sm font-semibold tabular-nums"
                                                aria-live="polite"
                                                >{{
                                                    extras[item.id] ?? 0
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="flex size-11 items-center justify-center rounded-full border border-blue-600 text-blue-600 disabled:opacity-40"
                                                :aria-label="`Add one ${item.name}`"
                                                :disabled="
                                                    (extras[item.id] ?? 0) >=
                                                    MAX_EXTRA
                                                "
                                                @click="changeExtra(item.id, 1)"
                                            >
                                                <Plus
                                                    class="h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                        </span>
                                    </li>
                                </ul>
                                <p
                                    v-if="errors.additional_items"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.additional_items }}
                                </p>
                            </div>
                            <div v-if="inStockProducts.length" class="mt-4">
                                <p
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Products to buy (optional)
                                </p>
                                <ul
                                    class="mt-1 divide-y divide-border rounded-xl border border-border"
                                >
                                    <li
                                        v-for="product in inStockProducts"
                                        :key="product.id"
                                        class="flex items-center justify-between gap-3 px-3 py-2"
                                    >
                                        <span class="min-w-0 text-sm">
                                            <span class="block font-medium">{{
                                                product.name
                                            }}</span>
                                            <span
                                                class="block text-xs text-muted-foreground"
                                                >{{ product.pack_size }}
                                                {{ product.unit }} ·
                                                {{ money(product.price) }}</span
                                            >
                                        </span>
                                        <span
                                            class="flex shrink-0 items-center gap-1"
                                        >
                                            <button
                                                type="button"
                                                class="flex size-11 items-center justify-center rounded-full border border-border disabled:opacity-40"
                                                :aria-label="`Remove one ${product.name}`"
                                                :disabled="!cart[product.id]"
                                                @click="changeCart(product, -1)"
                                            >
                                                <Minus
                                                    class="h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                            <span
                                                class="w-6 text-center text-sm font-semibold tabular-nums"
                                                aria-live="polite"
                                                >{{
                                                    cart[product.id] ?? 0
                                                }}</span
                                            >
                                            <button
                                                type="button"
                                                class="flex size-11 items-center justify-center rounded-full border border-blue-600 text-blue-600 disabled:opacity-40"
                                                :aria-label="`Add one ${product.name}`"
                                                :disabled="
                                                    (cart[product.id] ?? 0) >=
                                                    product.max_count
                                                "
                                                @click="changeCart(product, 1)"
                                            >
                                                <Plus
                                                    class="h-4 w-4"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                        </span>
                                    </li>
                                </ul>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Handed over with your laundry.
                                </p>
                                <p
                                    v-if="errors.retail_items"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.retail_items }}
                                </p>
                            </div>
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
                                    v-if="shop.offers_pickup"
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
                            <p
                                v-if="shop.offers_pickup && riderLimitText"
                                class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground"
                            >
                                <Weight
                                    class="h-4 w-4 shrink-0"
                                    aria-hidden="true"
                                />
                                {{ riderLimitText }}
                            </p>
                            <p
                                v-if="
                                    form.pickup_type === 'pickup' &&
                                    (pickupWeightProblem || errors.pickup_type)
                                "
                                class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"
                                role="alert"
                            >
                                {{ pickupWeightProblem ?? errors.pickup_type }}
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
                                @blur="resolvePickupPin"
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

                        <!-- Alternate contact (pickup only) -->
                        <template v-if="form.pickup_type === 'pickup'">
                            <div>
                                <label
                                    for="order-alt-name"
                                    class="text-sm font-semibold text-muted-foreground"
                                    >Alternate contact name</label
                                >
                                <Input
                                    id="order-alt-name"
                                    v-model="form.alternate_contact_name"
                                    maxlength="150"
                                    placeholder="e.g. a family member or neighbour"
                                    :class="[
                                        fieldClass,
                                        errors.alternate_contact_name &&
                                            'border-red-400',
                                    ]"
                                />
                                <p
                                    v-if="errors.alternate_contact_name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.alternate_contact_name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="order-alt-phone"
                                    class="text-sm font-semibold text-muted-foreground"
                                    >Alternate contact number</label
                                >
                                <PhilippinePhoneInput
                                    id="order-alt-phone"
                                    v-model="form.alternate_contact_phone"
                                    :class="[
                                        fieldClass,
                                        errors.alternate_contact_phone &&
                                            'border-red-400',
                                    ]"
                                />
                                <p
                                    v-if="errors.alternate_contact_phone"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ errors.alternate_contact_phone }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Someone the rider can call, or hand the
                                    laundry to, if you are not home.
                                </p>
                            </div>
                        </template>

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

        <!-- ── Products checkout sheet ─────────────────────────── -->
        <Sheet v-model:open="isProductCheckoutOpen">
            <SheetContent
                side="bottom"
                class="mx-auto max-h-[92dvh] max-w-lg gap-0 overflow-y-auto rounded-t-[28px] border-t-0 p-0"
            >
                <div class="px-6 pt-6 pb-4">
                    <SheetTitle class="text-xl font-bold"
                        >Buy products</SheetTitle
                    >
                    <SheetDescription class="mt-1 text-sm">
                        Collect them at {{ shop.shop_name }} once the shop marks
                        them ready.
                    </SheetDescription>
                </div>
                <form
                    class="space-y-5 px-6 pb-8"
                    @submit.prevent="placeProductOrder"
                >
                    <ul
                        class="divide-y divide-border rounded-xl border border-border"
                    >
                        <template
                            v-for="product in retailProducts"
                            :key="product.id"
                        >
                            <li
                                v-if="cart[product.id]"
                                class="flex items-center justify-between gap-3 px-3 py-2"
                            >
                                <span class="min-w-0 text-sm">
                                    <span class="block font-medium">{{
                                        product.name
                                    }}</span>
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >{{ product.pack_size }}
                                        {{ product.unit }} ·
                                        {{ money(product.price) }}</span
                                    >
                                </span>
                                <span class="flex shrink-0 items-center gap-1">
                                    <button
                                        type="button"
                                        class="flex size-11 items-center justify-center rounded-full border border-border"
                                        :aria-label="`Remove one ${product.name}`"
                                        @click="changeCart(product, -1)"
                                    >
                                        <Minus
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                    <span
                                        class="w-6 text-center text-sm font-semibold tabular-nums"
                                        aria-live="polite"
                                        >{{ cart[product.id] }}</span
                                    >
                                    <button
                                        type="button"
                                        class="flex size-11 items-center justify-center rounded-full border border-blue-600 text-blue-600 disabled:opacity-40"
                                        :aria-label="`Add one ${product.name}`"
                                        :disabled="
                                            cart[product.id] >=
                                            product.max_count
                                        "
                                        @click="changeCart(product, 1)"
                                    >
                                        <Plus
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </span>
                            </li>
                        </template>
                    </ul>
                    <p v-if="errors.retail_items" class="text-xs text-red-500">
                        {{ errors.retail_items }}
                    </p>

                    <div>
                        <label
                            for="product-name"
                            class="text-sm font-medium text-muted-foreground"
                            >Your name</label
                        >
                        <Input
                            id="product-name"
                            v-model="productForm.customer_name"
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
                            for="product-phone"
                            class="text-sm font-medium text-muted-foreground"
                            >Mobile number</label
                        >
                        <PhilippinePhoneInput
                            id="product-phone"
                            v-model="productForm.customer_phone"
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
                    <div>
                        <label
                            for="product-notes"
                            class="text-sm font-medium text-muted-foreground"
                            >Note for the shop (optional)</label
                        >
                        <Input
                            id="product-notes"
                            v-model="productForm.special_instructions"
                            maxlength="500"
                            :class="fieldClass"
                        />
                    </div>

                    <BillingBreakdown
                        v-if="productEstimate"
                        :breakdown="productEstimate"
                        :show-payment="false"
                    />

                    <p class="flex items-start gap-2 text-xs text-slate-500">
                        <MapPin
                            class="mt-0.5 h-3.5 w-3.5 shrink-0"
                            aria-hidden="true"
                        />
                        <span>
                            Pickup at {{ formatAddress(shop) }}.
                            {{
                                shop.payment_options.gcash === 'online'
                                    ? 'Pay in cash at the shop, or online with GCash or Maya from your order page.'
                                    : 'Pay in cash when you collect them.'
                            }}
                        </span>
                    </p>
                    <p
                        v-if="errors.shop_id"
                        class="text-xs text-red-500"
                        role="alert"
                    >
                        {{ errors.shop_id }}
                    </p>

                    <button
                        type="submit"
                        class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        :disabled="
                            placingProducts || productCheckoutHint !== ''
                        "
                    >
                        <Loader2
                            v-if="placingProducts"
                            class="h-4 w-4 animate-spin"
                            aria-hidden="true"
                        />
                        Place order
                    </button>
                    <p
                        class="min-h-5 text-center text-sm text-muted-foreground"
                        role="status"
                    >
                        {{ productCheckoutHint }}
                    </p>
                </form>
            </SheetContent>
        </Sheet>
    </UserLayout>
</template>
