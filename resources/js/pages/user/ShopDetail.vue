<script setup lang="ts">
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import CustomerServiceAgreement from '@/components/customer/CustomerServiceAgreement.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import UserLayout from '@/layouts/user/UserLayout.vue';
import type { AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    CheckCircle2,
    ChevronRight,
    Clock,
    CreditCard,
    Info,
    Loader2,
    MapPin,
    Package,
    Phone,
    Smartphone,
    WashingMachine,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Types ────────────────────────────────────────────────────────────────────

interface ShopService {
    id: number;
    service_name: string;
    description: string | null;
    pricing_model: 'per_kg' | 'per_bundle';
    price_per_kg: string | null;
    bundle_weight_kg: string | null;
    bundle_price: string | null;
    estimated_hours: number | null;
}

interface ShopInfo {
    id: number;
    shop_name: string;
    branch_name: string | null;
    phone: string | null;
    block_street: string | null;
    municipality: string;
    barangay: string;
    cover_photo: string | null;
    gcash_qr: string | null;
    maya_qr: string | null;
    latitude: number | null;
    longitude: number | null;
    distance_km: number | null;
    offers_pickup: boolean;
    offers_delivery: boolean;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    shop: ShopInfo;
    services: ShopService[];
    userLat: number | null;
    userLng: number | null;
    customerAgreement: { title: string; version: string; content: string; effective_at: string };
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
        note: 'Pay cash when you drop off or pick up.',
    },
    {
        value: 'gcash',
        label: 'GCash',
        icon: Smartphone,
        note: 'Shop will send a GCash payment request.',
    },
    {
        value: 'maya',
        label: 'Maya',
        icon: CreditCard,
        note: 'Shop will send a Maya payment request.',
    },
];

const form = ref({
    shop_id: props.shop.id,
    service_id: 0,
    customer_name: user.value.name,
    customer_phone: '',
    estimated_weight_kg: '',
    pickup_type: 'walk_in' as 'walk_in' | 'pickup',
    payment_method: 'cash' as 'cash' | 'gcash' | 'maya',
    customer_address: '',
    special_instructions: '',
    customer_agreement_version: props.customerAgreement.version,
    customer_agreement_accepted: false,
});

function openOrder(service: ShopService) {
    selectedService.value = service;
    form.value.service_id = service.id;
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

const estimatedTotal = computed(() => {
    if (!selectedService.value) return null;
    const svc = selectedService.value;
    const wt = parseFloat(form.value.estimated_weight_kg);
    if (!wt || wt <= 0) return null;
    if (svc.pricing_model === 'per_kg' && svc.price_per_kg)
        return (parseFloat(svc.price_per_kg) * wt).toFixed(2);
    if (svc.pricing_model === 'per_bundle' && svc.bundle_price) {
        const bundleSize = svc.bundle_weight_kg
            ? parseFloat(svc.bundle_weight_kg)
            : 1;
        const bundles = Math.ceil(wt / bundleSize);
        return (bundles * parseFloat(svc.bundle_price)).toFixed(2);
    }
    return null;
});

function submitOrder() {
    placing.value = true;
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
    return '—';
}

// Service type icon colour
const serviceColours = [
    'bg-blue-100 text-blue-600',
    'bg-teal-100 text-teal-600',
    'bg-violet-100 text-violet-600',
    'bg-amber-100 text-amber-600',
];
function svcColour(idx: number) {
    return serviceColours[idx % serviceColours.length];
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
                <p v-if="shop.branch_name" class="mt-0.5 text-sm text-white/80">
                    {{ shop.branch_name }}
                </p>
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

                <div v-else class="space-y-3">
                    <div
                        v-for="(svc, idx) in services"
                        :key="svc.id"
                        class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm"
                    >
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div
                                    :class="`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${svcColour(idx)}`"
                                >
                                    <WashingMachine class="h-5 w-5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex items-start justify-between gap-2"
                                    >
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-gray-800"
                                            >
                                                {{ svc.service_name }}
                                            </p>
                                            <p
                                                v-if="svc.description"
                                                class="mt-0.5 line-clamp-2 text-xs text-gray-500"
                                            >
                                                {{ svc.description }}
                                            </p>
                                        </div>
                                        <p
                                            class="shrink-0 text-base font-extrabold text-blue-600"
                                        >
                                            {{ formatServicePrice(svc) }}
                                        </p>
                                    </div>

                                    <div class="mt-2.5 flex items-center gap-2">
                                        <div
                                            v-if="svc.estimated_hours"
                                            class="flex items-center gap-1 rounded-full bg-gray-50 px-2 py-1 text-[11px] text-gray-400"
                                        >
                                            <Clock class="h-3 w-3" />
                                            ~{{ svc.estimated_hours }}h
                                        </div>
                                        <div
                                            class="flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[11px] text-amber-600"
                                        >
                                            <Info class="h-3 w-3" />
                                            Final price based on actual weight
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order CTA bar -->
                        <button
                            class="flex w-full items-center justify-between bg-blue-600 px-4 py-3 text-white transition hover:bg-blue-700 active:bg-blue-800"
                            @click="openOrder(svc)"
                        >
                            <span class="text-sm font-semibold"
                                >Place Order</span
                            >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
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

                    <!-- Estimated weight -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >Estimated Weight (kg)
                            <span class="text-red-500">*</span></label
                        >
                        <Input
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
                            v-if="errors.estimated_weight_kg"
                            class="text-xs text-red-500"
                        >
                            {{ errors.estimated_weight_kg }}
                        </p>
                        <div
                            v-if="estimatedTotal"
                            class="flex items-center gap-1.5 rounded-lg bg-green-50 p-2 text-xs text-green-700"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5 shrink-0" />
                            Estimated total:
                            <strong>₱{{ estimatedTotal }}</strong>
                            <span class="opacity-70">(indicative)</span>
                        </div>
                    </div>

                    <!-- Pickup type -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium"
                            >How will you drop-off?
                            <span class="text-red-500">*</span></label
                        >
                        <div
                            class="grid gap-2"
                            :class="shop.offers_pickup ? 'grid-cols-2' : 'grid-cols-1'"
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
                                <component
                                    :is="opt.icon"
                                    class="h-4 w-4 shrink-0"
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
                                    <p class="truncate text-xs text-gray-400">
                                        {{ opt.note }}
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

                    <CustomerServiceAgreement v-model="form.customer_agreement_accepted" :agreement="customerAgreement" />
                    <p v-if="errors.customer_agreement_accepted" class="text-xs text-red-500">{{ errors.customer_agreement_accepted }}</p>

                    <!-- Disclaimer -->
                    <div
                        class="rounded-xl bg-amber-50 p-3 text-xs text-amber-700"
                    >
                        <strong>Note:</strong> Estimated total is indicative —
                        the shop will weigh your items and confirm the final
                        price. Payment via
                        <strong>{{ selectedPayment?.label }}</strong> will be
                        collected once the shop sets the final amount.
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
